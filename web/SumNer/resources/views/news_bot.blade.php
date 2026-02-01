<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Bot: Summarization & NER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Canvas Background Fix */
        #canvas {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none; z-index: 0;
        }

        /* Hide scrollbar for cleaner look */
        textarea::-webkit-scrollbar { width: 8px; }
        textarea::-webkit-scrollbar-track { background: transparent; }
        textarea::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.1); border-radius: 4px; }
    </style>
</head>
<body class="bg-gradient-to-b from-[#e6eff8] to-[#eef2f9] min-h-screen flex justify-center text-[#333] p-4 sm:p-6">

    <canvas id="canvas"></canvas>

    <div class="w-full max-w-[1100px] flex flex-col relative z-10">
        
        <header class="flex justify-between items-center py-4 mb-8">
            <div class="flex items-center gap-4">
                <button class="text-gray-600 hover:text-[#1a2a6c] transition">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <a href="/" class="flex items-center font-bold text-lg tracking-wide text-[#333] no-underline gap-2">
                    <span>SUMMER</span>
                </a>
            </div>

            <nav class="bg-white p-1 rounded-full flex items-center border border-[rgba(0,0,0,0.15)]">
                <span class="px-4 py-2 text-xs font-medium text-gray-400 italic border-r border-gray-100 hidden sm:block">
                    Guest Mode
                </span>
                <a href="#" class="px-5 py-2 rounded-full font-semibold text-sm text-white bg-[#4a6fa5] hover:opacity-90 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
            </nav>
        </header>

        <main class="flex-1 flex flex-col items-center w-full">
            
            <h1 class="text-3xl sm:text-4xl font-bold text-[#222] mb-8 text-center tracking-tight">
                News Bot: Summarization & NER
            </h1>

            <div class="w-full max-w-4xl bg-white p-6 sm:p-8 rounded-[32px] border border-[rgba(0,0,0,0.15)] flex flex-col gap-6 shadow-none">
                
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    
                    <label class="cursor-pointer group flex items-center gap-2 px-5 py-2.5 bg-gray-50 hover:bg-gray-100 border border-[rgba(0,0,0,0.15)] rounded-2xl transition-all w-full sm:w-auto justify-center">
                        <i class="fa-solid fa-cloud-arrow-up text-[#4a6fa5] group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-semibold text-gray-600">Upload PDF</span>
                        <input type="file" class="hidden" accept=".pdf">
                    </label>

                    <div class="flex items-center gap-3 px-4 py-2.5 border border-[rgba(0,0,0,0.15)] rounded-2xl bg-white w-full sm:w-auto">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">TYPE</span>
                        <div class="h-4 w-px bg-gray-200"></div>
                        <select class="bg-transparent border-none text-sm font-semibold text-gray-700 focus:ring-0 cursor-pointer outline-none">
                            <option>Abstractive</option>
                            <option>Extractive</option>
                        </select>
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 ml-2"></i>
                    </div>
                </div>

                <div class="relative w-full">
                    <textarea 
                        class="w-full h-64 bg-gray-50 rounded-2xl border border-[rgba(0,0,0,0.15)] p-6 text-gray-700 placeholder-gray-400 focus:bg-white focus:border-[#4a6fa5] focus:ring-4 focus:ring-[#4a6fa5]/10 transition-all outline-none resize-none text-base leading-relaxed"
                        placeholder="Paste your news article here..."></textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    
                    <button class="flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-gray-50 border border-[rgba(0,0,0,0.15)] rounded-xl text-gray-600 font-medium text-sm transition-all w-full sm:w-auto justify-center">
                        <i class="fa-regular fa-clipboard"></i>
                        Paste
                    </button>

                    <button class="relative group w-full sm:w-auto rounded-full p-[2px] overflow-hidden shadow-none transform-none">

                        <div class="absolute inset-[-100%] bg-[conic-gradient(from_0deg,#ff4545,#ffa534,#ffe234,#57ff34,#34e1ff,#3456ff,#b834ff,#ff4545)] opacity-0 group-hover:opacity-100 transition-opacity duration-500 animate-[spin_3s_linear_infinite]"></div>

                        <div class="relative z-10 flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-[#282828] via-[#2f2f2f] to-[#5f5f5f] text-white rounded-full font-semibold text-sm h-full w-full leading-none">
                            Analyze Content
                            <i class="fa-solid fa-wand-magic-sparkles ml-1"></i>
                        </div>

                    </button>
                </div>

            </div>
        </main>
    </div>

    <script>
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        let width, height, particles = [];

        const properties = {
            particleCount: 70,
            particleSpeed: 0.4,
            mouseRadius: 150,
            starBaseSize: 1.5,
            starBaseOpacity: 0.15,
            lineLength: 120,
            lineColor: '50, 50, 50' 
        };

        let mouse = { x: undefined, y: undefined };

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }

        window.addEventListener('resize', () => { resize(); initParticles(); });
        window.addEventListener('mousemove', (e) => { mouse.x = e.x; mouse.y = e.y; });
        window.addEventListener('mouseout', () => { mouse.x = undefined; mouse.y = undefined; });

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.velocityX = (Math.random() - 0.5) * properties.particleSpeed;
                this.velocityY = (Math.random() - 0.5) * properties.particleSpeed;
                this.size = Math.random() * properties.starBaseSize + 0.5;
            }
            update() {
                this.x += this.velocityX;
                this.y += this.velocityY;
                if(this.x<0 || this.x>width) this.velocityX *= -1;
                if(this.y<0 || this.y>height) this.velocityY *= -1;
            }
            draw() {
                let dx = mouse.x - this.x, dy = mouse.y - this.y;
                let dist = Math.sqrt(dx*dx + dy*dy);
                let opacity = properties.starBaseOpacity;
                let size = this.size;
                if(mouse.x && dist < properties.mouseRadius) {
                    let intensity = 1 - (dist/properties.mouseRadius);
                    opacity += intensity * 0.8;
                    size += intensity * 3;
                }
                ctx.beginPath();
                ctx.arc(this.x, this.y, size, 0, Math.PI*2);
                ctx.fillStyle = `rgba(50, 50, 50, ${opacity})`;
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            for(let i=0; i<properties.particleCount; i++) particles.push(new Particle());
        }

        function connectParticles() {
            for(let a=0; a<particles.length; a++){
                for(let b=a; b<particles.length; b++){
                    let dx = particles[a].x - particles[b].x;
                    let dy = particles[a].y - particles[b].y;
                    let dist = Math.sqrt(dx*dx + dy*dy);
                    if(dist < properties.lineLength){
                        let opacity = (1 - (dist/properties.lineLength)) * 0.2;
                        ctx.strokeStyle = `rgba(${properties.lineColor}, ${opacity})`;
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(particles[a].x, particles[a].y);
                        ctx.lineTo(particles[b].x, particles[b].y);
                        ctx.stroke();
                    }
                }
            }
        }

        function animate() {
            ctx.clearRect(0,0,width,height);
            particles.forEach(p => { p.update(); p.draw(); });
            connectParticles();
            requestAnimationFrame(animate);
        }

        resize(); initParticles(); animate();
    </script>
</body>
</html>