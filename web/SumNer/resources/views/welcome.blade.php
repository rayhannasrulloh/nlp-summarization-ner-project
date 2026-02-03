<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUMMER AI</title>
    <link rel="shortcut icon" href="{{ asset('logo-summer-gray.png') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        ::-moz-selection {color: white;background: #072c64;}
        ::selection {color: white;background: #072c64;}

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, #eef2f9 0%, #fff 100%);
            color: #333;
            width: 100%; min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        #canvas {
            position: fixed; /* Ganti absolute jadi fixed agar tetap full screen saat scroll */
            top: 0;left: 0;
            display: block;
            width: 100%;
            height: 100%;
            pointer-events: none;z-index: 0;
        }

        .app-container {
            width: 100%;
            max-width: 1100px;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 150px;
            margin-inline: -250px;
            margin-bottom: 40px;
            margin-top: -20px;
            /* border: 1px solid rgba(0, 0, 0, 0.15); */
        }

        .logo {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 1px;
            text-decoration: none;
            color: #333;
        }

        .logo svg { margin-right: 10px; }

        nav {
            background-color: #fff;
            padding: 5px;
            border-radius: 30px;
            display: flex;
            border: 1px solid rgba(0, 0, 0, 0.15);
        }

        .nav-item {
            font-family: 'Inter', sans-serif; font-weight: 500;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 25px; border: 1px solid rgba(0, 0, 0, 0.15);
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: inline-block;
        }

        .nav-item:hover {
            color: #333;
            background-color: #f7f9fc;
        }

        .nav-item.active {
            background-color: #f0f2f5;
            color: #333;
            font-weight: 600;
        }

        .nav-item.cta {
            color: #333;
            font-weight: 600;
        }

        /* Main Content */
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Hero Section */
        .hero {
            text-align: center;
            margin-bottom: 50px;
            max-width: 1000px;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.1;
            color: #222;
        }

        .hero p {
            font-size: 1.15rem;
            color: #666;
            line-height: 1.5;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Styling Button Hero */
        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px; /* Jarak antara teks dan panah */
            margin-top: 35px;
            padding: 16px 36px;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.15);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            color: #333333;
            
            /* ANIMASI BACKGROUND */
            /* Kita buat ukuran background 200% agar bisa digeser */
            background: linear-gradient(45deg, #ffffff, #ededed, #ffffff); 
            background-size: 200% auto;
            
            /* Transisi untuk semua properti */
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            
            /* Shadow awal (lembut) */
            
            /* Pastikan tombol ada di atas canvas interaktif */
            position: relative;
            z-index: 10;
        }

        /* State Hover (Saat kursor menyentuh) */
        .hero-btn:hover {
            /* Menggeser posisi background (efek mengalir) */
            background-position: right center;
            
            /* Tombol naik sedikit */
            transform: translateY(-1px);
            
            /* Shadow menjadi lebih besar dan berwarna (Glowing Effect) */
            /* box-shadow: 0 10px 25px rgba(26, 42, 108, 0.4); */
        }

        /* Animasi Ikon Panah */
        .hero-btn i {
            transition: transform 0.3s ease;
        }

        .hero-btn:hover i {
            /* Panah bergerak ke kanan saat hover */
            transform: translateX(5px);
        }

        /* State Active (Saat diklik) */
        .hero-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(160, 160, 160, 0.3);
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            width: 100%;
            max-width: 950px;
            margin-bottom: 50px;
        }

        .feature-card {
            /* Style lama tetap ada */
            background-color: #fff;
            padding: 35px;
            border-radius: 24px;
            
            /* GANTI border lama dengan ini (transparan default) */
            border: 1px solid rgba(0, 0, 0, 0.1); 
            
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            
            /* PENTING: Kita pisah transisinya */
            /* Transform untuk efek angkat (hover biasa) tetap ada */
            transition: transform 0.2s ease; 
            
            /* Box-shadow & border di-handle JS supaya tidak delay/lag saat mouse gerak */
            /* Jangan tambahkan transition untuk box-shadow disini */
        }

        /* Hapus atau sesuaikan hover default agar tidak bentrok */
        .feature-card:hover {
            /* transform: translateY(-5px); */
            /* Shadow akan diurus oleh JS */
        }

        .feature-card i {
            font-size: 1.4rem;
            margin-bottom: 20px;
            color: #444;
            background-color: #f0f2f5;
            padding: 12px;
            border-radius: 14px;
            border: 1px solid rgba(0, 0, 0, 0.15);
        }

        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: #222;
        }

        .feature-card p {
            font-size: 1rem;
            color: #666;
            line-height: 1.5;
        }

        /* Process Section */
        .process-section {
            background-color: #fff;
            padding: 50px;
            border-radius: 24px;
            border: 1px solid rgba(0, 0, 0, 0.15);
            text-align: center;
            width: 100%;
            max-width: 950px;
            /* box-shadow: 0 4px 12px rgba(0,0,0,0.03); */
        }

        .process-section h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #222;
        }

        .process-section p {
            color: #666;
            margin-bottom: 40px;
            font-size: 1.05rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .process-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .step {
            display: flex;
            align-items: center;
            background-color: #f0f2f5;
            padding: 12px 24px;
            border-radius: 30px;
            border: 1px solid rgba(0, 0, 0, 0.15);
        }

        .step-number {
            background-color: #fff;
            color: #333;
            font-weight: 600;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 12px;
            /* box-shadow: 0 2px 4px rgba(0,0,0,0.05); */
        }

        .step-text {
            font-weight: 500;
            font-size: 1rem;
        }

        .separator {
            color: #ccc;
            font-size: 0.8rem;
        }

        /* Footer */
        footer {
            text-align: center;
            margin-top: 60px;
            padding: 20px 0;
            color: #666;
        }

        footer p.brand {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 1.1rem;
        }

        .copyright {
            font-size: 0.9rem;
            color: #888;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header { flex-direction: column; gap: 20px; }
            .hero h1 { font-size: 3rem; }
            .features-grid { grid-template-columns: 1fr; }
            .process-steps { flex-direction: column; gap: 10px; }
            .separator { transform: rotate(90deg); }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header>
            <a href="#" class="logo">
                <img src="{{ asset('logo-summer-gray.png') }}" alt="SUMMER Logo" width="32" height="32">
                <span>SUMMER</span>
            </a>
            <nav>
                <a href="#" class="nav-item active">Home</a>
                <a href="{{ route('login') }}" class="nav-item cta">Try Now</a>
            </nav>
        </header>

        <canvas id="canvas"></canvas>

        <main>
            <section id="landing-page">
                <div class="hero">
                    <h1>Summer AI</h1>
                    <p>Our platform leverages the latest in Generative AI to provide more than just text shortening.</p>
                    <a href="{{ route('login') }}" class="hero-btn">
                        <span>Try Summer Now</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fa-solid fa-bolt"></i>
                        <h3>Instant Summaries</h3>
                        <p>Get to the point fast. Our AI condenses complex articles into concise executive summaries.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fa-solid fa-tags"></i>
                        <h3>Entity Recognition</h3>
                        <p>Automatically extract and classify key entities like Names, Organizations, and Locations.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fa-solid fa-file-lines"></i>
                        <h3>Document Analysis</h3>
                        <p>Upload document reports or text files. We handle text extraction and formatting for you.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fa-solid fa-chart-simple"></i>
                        <h3>Visual Data</h3>
                        <p>Identify key themes instantly with generated word clouds and frequency analysis visualizations.</p>
                    </div>
                </div>

                <div class="process-section">
                    <h2>Streamline your information intake</h2>
                    <p>Whether you are a researcher, student, or professional, Summer AI helps you digest information 10x faster.</p>
                    <div class="process-steps">
                        <div class="step">
                            <span class="step-number">1</span>
                            <span class="step-text">Input Source</span>
                        </div>
                        <i class="fa-solid fa-chevron-right separator"></i>
                        <div class="step">
                            <span class="step-number">2</span>
                            <span class="step-text">AI Processing</span>
                        </div>
                        <i class="fa-solid fa-chevron-right separator"></i>
                        <div class="step">
                            <span class="step-number">3</span>
                            <span class="step-text">Export & Share</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <p class="brand">Summer AI</p>
            <p class="copyright" style="margin-top: 5px; font-size: 0.8rem;">Powered by FastAPI & Transformers</p>
            <p class="copyright">@2026 Summer AI. Built with ❤️.</p>
        </footer>
    </div>

    <script>
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];

        const properties = {
            particleCount: 100,     // Kurangi sedikit jumlah partikel agar tidak terlalu semrawut dengan garis
            particleSpeed: 0.5,
            mouseRadius: 150,
            starBaseSize: 1.8,
            starBaseOpacity: 0.15,
            // Properti baru untuk garis
            lineLength: 120,        // Jarak maksimal untuk menarik garis
            lineColor: '50, 50, 50' // Warna garis (RGB) - abu gelap
        };

        let mouse = {
            x: undefined,
            y: undefined
        };

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }

        window.addEventListener('resize', () => {
            resize();
            initParticles();
        });

        window.addEventListener('mousemove', (event) => {
            mouse.x = event.x;
            mouse.y = event.y;
        });

        window.addEventListener('mouseout', () => {
            mouse.x = undefined;
            mouse.y = undefined;
        });

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

                if (this.x < 0 || this.x > width) this.velocityX *= -1;
                if (this.y < 0 || this.y > height) this.velocityY *= -1;
            }

            draw() {
                let dx = mouse.x - this.x;
                let dy = mouse.y - this.y;
                let distance = Math.sqrt(dx*dx + dy*dy);
                
                let opacity = properties.starBaseOpacity;
                let currentSize = this.size;

                // Efek Spotlight (Membesar saat dekat mouse)
                if (mouse.x !== undefined && distance < properties.mouseRadius) {
                    const intensity = 1 - (distance / properties.mouseRadius);
                    opacity = properties.starBaseOpacity + (intensity * 0.8);
                    currentSize = this.size + (intensity * 3);
                }

                ctx.beginPath();
                ctx.arc(this.x, this.y, currentSize, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(50, 50, 50, ${opacity})`;
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            for (let i = 0; i < properties.particleCount; i++) {
                particles.push(new Particle());
            }
        }

        // FUNGSI BARU: Menggambar garis antar partikel
        function connectParticles() {
            for (let a = 0; a < particles.length; a++) {
                for (let b = a; b < particles.length; b++) {
                    let dx = particles[a].x - particles[b].x;
                    let dy = particles[a].y - particles[b].y;
                    let distance = Math.sqrt(dx * dx + dy * dy);

                    // Jika jarak antar dua partikel kurang dari batas (lineLength)
                    if (distance < properties.lineLength) {
                        // Hitung opacity: semakin dekat semakin tebal, semakin jauh semakin hilang
                        let opacity = 1 - (distance / properties.lineLength);
                        
                        // Supaya garisnya halus, kita buat agak transparan
                        opacity = opacity * 0.2; 

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

        const cards = document.querySelectorAll('.feature-card');

        function handleCardGlow() {
            // Konfigurasi Glow
            const glowRadius = 300; // Jarak cursor mulai mempengaruhi card
            const glowColor = '150, 150, 150';
            
            cards.forEach(card => {
                // Ambil posisi & ukuran card
                const rect = card.getBoundingClientRect();
                
                // Hitung titik tengah card
                const cardX = rect.left + rect.width / 2;
                const cardY = rect.top + rect.height / 2;

                // Hitung jarak mouse ke tengah card
                // Kita pakai mouse.x dan mouse.y dari script canvas sebelumnya
                if (mouse.x && mouse.y) {
                    const dist = Math.hypot(mouse.x - cardX, mouse.y - cardY);

                    if (dist < glowRadius) {
                        // Hitung intensitas (0 sampai 1)
                        // Semakin dekat, semakin mendekati 1
                        const intensity = 1 - (dist / glowRadius);
                        
                        // Buat efek glow biru gelap
                        // Border menjadi lebih solid
                        const borderAlpha = 0.1 + (intensity * 0.8); 
                        // Shadow menyebar
                        const shadowAlpha = intensity * 0.3; 
                        const shadowBlur = intensity * 20;

                        card.style.borderColor = `rgba(${glowColor}, ${borderAlpha})`;
                        card.style.boxShadow = `0 4px ${shadowBlur}px rgba(${glowColor}, ${shadowAlpha})`;
                    } else {
                        // Reset jika mouse jauh
                        card.style.borderColor = 'rgba(0, 0, 0, 0.1)';
                        card.style.boxShadow = 'none';
                    }
                } else {
                    // Reset jika mouse keluar layar
                    card.style.borderColor = 'rgba(0, 0, 0, 0.1)';
                    card.style.boxShadow = 'none';
                }
            });
        }

        // --- UPDATE FUNGSI ANIMATE ---
        function animate() {
            ctx.clearRect(0, 0, width, height);
            
            particles.forEach(particle => {
                particle.update();
                particle.draw();
            });
            
            connectParticles();
            
            // PANGGIL FUNGSI GLOW DI SINI SETIAP FRAME
            handleCardGlow(); 

            requestAnimationFrame(animate);
        }

        resize();
        initParticles();
        animate();
    </script>
</body>
</html>