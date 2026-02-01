import os
import argparse
import numpy as np

from datasets import load_dataset
import evaluate

from transformers import (
    AutoTokenizer,
    AutoModelForSeq2SeqLM,
    DataCollatorForSeq2Seq,
    Seq2SeqTrainer,
    Seq2SeqTrainingArguments,
)


def parse_args():
    p = argparse.ArgumentParser()

    # output
    p.add_argument("--output_dir", type=str, default="outputs/distilbart_finetuned")

    # model + data
    p.add_argument("--model_checkpoint", type=str, default="sshleifer/distilbart-cnn-12-6")
    p.add_argument("--dataset_name", type=str, default="abisee/cnn_dailymail")
    p.add_argument("--dataset_config", type=str, default="3.0.0")

    # lengths (aman untuk 4GB)
    p.add_argument("--max_input_length", type=int, default=256)
    p.add_argument("--max_target_length", type=int, default=96)

    # subset (0 = full)
    p.add_argument("--train_size", type=int, default=5000)   # ✅ rencana kamu
    p.add_argument("--val_size", type=int, default=625)      # ✅ rencana kamu

    # training
    p.add_argument("--epochs", type=int, default=3)
    p.add_argument("--lr", type=float, default=3e-5)
    p.add_argument("--weight_decay", type=float, default=0.01)

    p.add_argument("--train_bs", type=int, default=1)
    p.add_argument("--eval_bs", type=int, default=1)
    p.add_argument("--grad_accum", type=int, default=16)

    p.add_argument("--logging_steps", type=int, default=50)

    # generation
    p.add_argument("--num_beams", type=int, default=4)

    # memory helpers
    p.add_argument("--fp16", action="store_true")
    p.add_argument("--gradient_checkpointing", action="store_true")

    # reproducibility
    p.add_argument("--seed", type=int, default=42)

    return p.parse_args()


def main():
    args = parse_args()
    os.makedirs(args.output_dir, exist_ok=True)

    print("Loading dataset...")
    dataset = load_dataset(args.dataset_name, args.dataset_config)

    train_ds = dataset["train"]
    val_ds = dataset["validation"]

    if args.train_size and args.train_size > 0:
        train_ds = train_ds.shuffle(seed=args.seed).select(range(args.train_size))
    if args.val_size and args.val_size > 0:
        val_ds = val_ds.shuffle(seed=args.seed).select(range(args.val_size))

    print("Loading tokenizer & model...")
    tokenizer = AutoTokenizer.from_pretrained(args.model_checkpoint)
    model = AutoModelForSeq2SeqLM.from_pretrained(args.model_checkpoint)

    if args.gradient_checkpointing:
        model.gradient_checkpointing_enable()
        model.config.use_cache = False

    def preprocess_function(batch):
        model_inputs = tokenizer(
            batch["article"],
            max_length=args.max_input_length,
            truncation=True,
            padding=False,  # dynamic padding
        )
        labels = tokenizer(
            text_target=batch["highlights"],
            max_length=args.max_target_length,
            truncation=True,
            padding=False,
        )
        model_inputs["labels"] = labels["input_ids"]
        return model_inputs

    print("Tokenizing...")
    tokenized_train = train_ds.map(preprocess_function, batched=True, remove_columns=train_ds.column_names)
    tokenized_val = val_ds.map(preprocess_function, batched=True, remove_columns=val_ds.column_names)

    rouge = evaluate.load("rouge")

    def compute_metrics(eval_pred):
        preds, labels = eval_pred
        if isinstance(preds, tuple):
            preds = preds[0]

        labels = np.where(labels != -100, labels, tokenizer.pad_token_id)

        decoded_preds = tokenizer.batch_decode(preds, skip_special_tokens=True)
        decoded_labels = tokenizer.batch_decode(labels, skip_special_tokens=True)

        result = rouge.compute(
            predictions=decoded_preds,
            references=decoded_labels,
            use_stemmer=True
        )
        # output keys biasanya rouge1 rouge2 rougeL rougeLsum
        return {k: round(v, 4) for k, v in result.items()}

    data_collator = DataCollatorForSeq2Seq(tokenizer=tokenizer, model=model, padding="longest",label_pad_token_id=-100)

    training_args = Seq2SeqTrainingArguments(
        output_dir=args.output_dir,

        # ✅ ini yang bikin ROUGE pasti muncul
        evaluation_strategy="epoch",
        save_strategy="epoch",

        logging_steps=args.logging_steps,
        save_total_limit=2,

        load_best_model_at_end=True,
        metric_for_best_model="rougeL",
        greater_is_better=True,

        learning_rate=args.lr,
        weight_decay=args.weight_decay,
        num_train_epochs=args.epochs,
        warmup_ratio=0.06,

        per_device_train_batch_size=args.train_bs,
        per_device_eval_batch_size=args.eval_bs,
        gradient_accumulation_steps=args.grad_accum,

        fp16=args.fp16,

        predict_with_generate=True,
        generation_num_beams=args.num_beams,
        generation_max_length=args.max_target_length,

        seed=args.seed,
        data_seed=args.seed,

        report_to="none",
    )

    trainer = Seq2SeqTrainer(
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

    # ✅ pastiin ROUGE final muncul di terminal (meskipun kamu cuma 1 epoch)
    print("Running final evaluation (ROUGE)...")
    final_metrics = trainer.evaluate()
    print("FINAL EVAL:", final_metrics)

    print("Saving final model...")
    trainer.save_model(args.output_dir)
    tokenizer.save_pretrained(args.output_dir)

    print("✅ Done. Saved to:", args.output_dir)


if __name__ == "__main__":
    main()
