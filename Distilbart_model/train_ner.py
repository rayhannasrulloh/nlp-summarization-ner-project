# train_ner.py
import os
import argparse
import numpy as np

from datasets import load_dataset
import evaluate

from transformers import (
    AutoTokenizer,
    AutoModelForTokenClassification,
    DataCollatorForTokenClassification,
    TrainingArguments,
    Trainer,
)

# Optional but nice: prevent HF tokenizer warning spam
os.environ["TOKENIZERS_PARALLELISM"] = "false"


def parse_args():
    p = argparse.ArgumentParser()

    # output
    p.add_argument("--output_dir", type=str, default="outputs/ner_distilbert")

    # model + data
    p.add_argument("--model_checkpoint", type=str, default="distilbert-base-cased")
    p.add_argument("--dataset_name", type=str, default="lhoestq/conll2003")
    p.add_argument("--seed", type=int, default=42)

    # subset (0 = full)
    p.add_argument("--train_size", type=int, default=0)
    p.add_argument("--val_size", type=int, default=0)

    # training
    p.add_argument("--epochs", type=int, default=3)
    p.add_argument("--lr", type=float, default=2e-5)
    p.add_argument("--weight_decay", type=float, default=0.01)

    p.add_argument("--train_bs", type=int, default=16)
    p.add_argument("--eval_bs", type=int, default=16)

    p.add_argument("--logging_steps", type=int, default=100)
    p.add_argument("--fp16", action="store_true")

    return p.parse_args()


def get_label_list_from_dataset(raw):
    """
    Robust label extraction for conll2003 across datasets versions / configs.
    """
    ner_feat = raw["train"].features.get("ner_tags", None)

    label_list = None

    # Case 1: Sequence(ClassLabel) => feature.names exists
    if ner_feat is not None and hasattr(ner_feat, "feature") and hasattr(ner_feat.feature, "names"):
        label_list = list(ner_feat.feature.names)

    # Case 2: ClassLabel directly => names exists
    elif ner_feat is not None and hasattr(ner_feat, "names"):
        label_list = list(ner_feat.names)

    # Fallback: CoNLL2003 standard (9 labels)
    if not label_list:
        label_list = [
            "O",
            "B-PER", "I-PER",
            "B-ORG", "I-ORG",
            "B-LOC", "I-LOC",
            "B-MISC", "I-MISC",
        ]

    return label_list


def main():
    args = parse_args()
    os.makedirs(args.output_dir, exist_ok=True)

    # --- GPU / FP16 sanity check (prevents confusing crashes) ---
    try:
        import torch
        cuda_ok = torch.cuda.is_available()
        if args.fp16 and not cuda_ok:
            print("⚠️  --fp16 requested but CUDA is not available. Disabling fp16.")
            args.fp16 = False
        print("CUDA available:", cuda_ok)
        if cuda_ok:
            print("GPU:", torch.cuda.get_device_name(0))
    except Exception as e:
        print("⚠️  Could not check CUDA:", e)

    print("Loading dataset:", args.dataset_name)
    raw = load_dataset(args.dataset_name)

    train_ds = raw["train"]
    val_ds = raw["validation"]

    if args.train_size and args.train_size > 0:
        train_ds = train_ds.shuffle(seed=args.seed).select(range(args.train_size))
    if args.val_size and args.val_size > 0:
        val_ds = val_ds.shuffle(seed=args.seed).select(range(args.val_size))

    # label names (robust & anti mismatch)
    label_list = get_label_list_from_dataset(raw)
    id2label = {i: l for i, l in enumerate(label_list)}
    label2id = {l: i for i, l in enumerate(label_list)}

    print("Labels:", label_list)

    print("Loading tokenizer & model:", args.model_checkpoint)
    tokenizer = AutoTokenizer.from_pretrained(args.model_checkpoint)

    model = AutoModelForTokenClassification.from_pretrained(
        args.model_checkpoint,
        num_labels=len(label_list),
        id2label=id2label,
        label2id=label2id,
    )

    def tokenize_and_align_labels(examples):
        tokenized = tokenizer(
            examples["tokens"],
            truncation=True,
            is_split_into_words=True,
        )

        aligned_labels = []
        for i, word_labels in enumerate(examples["ner_tags"]):
            word_ids = tokenized.word_ids(batch_index=i)
            prev_word_id = None
            label_ids = []

            for word_id in word_ids:
                if word_id is None:
                    label_ids.append(-100)
                elif word_id != prev_word_id:
                    label_ids.append(word_labels[word_id])  # label token pertama di word
                else:
                    label_ids.append(-100)  # token lanjutan di word -> ignore
                prev_word_id = word_id

            aligned_labels.append(label_ids)

        tokenized["labels"] = aligned_labels
        return tokenized

    print("Tokenizing...")
    tokenized_train = train_ds.map(
        tokenize_and_align_labels,
        batched=True,
        remove_columns=train_ds.column_names,
    )
    tokenized_val = val_ds.map(
        tokenize_and_align_labels,
        batched=True,
        remove_columns=val_ds.column_names,
    )

    data_collator = DataCollatorForTokenClassification(tokenizer=tokenizer)
    metric = evaluate.load("seqeval")

    def compute_metrics(eval_pred):
        logits, labels = eval_pred
        preds = np.argmax(logits, axis=-1)

        true_preds, true_labels = [], []
        for pred, lab in zip(preds, labels):
            cur_preds, cur_labs = [], []
            for p, l in zip(pred, lab):
                if l == -100:
                    continue
                # p = predicted label id, l = gold label id
                cur_preds.append(label_list[p])
                cur_labs.append(label_list[l])
            true_preds.append(cur_preds)
            true_labels.append(cur_labs)

        results = metric.compute(predictions=true_preds, references=true_labels)
        return {
            "precision": results["overall_precision"],
            "recall": results["overall_recall"],
            "f1": results["overall_f1"],
            "accuracy": results["overall_accuracy"],
        }

    training_args = TrainingArguments(
        output_dir=args.output_dir,

        # NOTE: use the safe, official arg name
        evaluation_strategy="epoch",
        save_strategy="epoch",

        save_total_limit=2,
        learning_rate=args.lr,
        weight_decay=args.weight_decay,
        num_train_epochs=args.epochs,

        per_device_train_batch_size=args.train_bs,
        per_device_eval_batch_size=args.eval_bs,

        logging_steps=args.logging_steps,
        fp16=args.fp16,

        seed=args.seed,
        data_seed=args.seed,

        report_to="none",

        load_best_model_at_end=True,
        metric_for_best_model="f1",
        greater_is_better=True,
    )

    trainer = Trainer(
        model=model,
        args=training_args,
        train_dataset=tokenized_train,
        eval_dataset=tokenized_val,
        tokenizer=tokenizer,
        data_collator=data_collator,
        compute_metrics=compute_metrics,
    )

    print("Start training...")
    trainer.train()

    print("Running final evaluation...")
    final_metrics = trainer.evaluate()
    print("FINAL EVAL:", final_metrics)

    print("Saving final model...")
    trainer.save_model(args.output_dir)
    tokenizer.save_pretrained(args.output_dir)

    print("✅ Done. Saved to:", args.output_dir)


if __name__ == "__main__":
    main()
