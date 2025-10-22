<?php
$page_title = 'Início';
include 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Crie seu Site Profissional em Minutos</h1>
            <p>Plataforma completa para criar e gerenciar o site da sua empresa sem complicação</p>
            <div class="hero-buttons">
                <a href="cadastro.php" class="btn btn-primary btn-large">Começar Agora</a>
                <a href="planos.php" class="btn btn-secondary btn-large">Ver Planos</a>
            </div>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2>Por que escolher a Sites Para Empresas?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3>Rápido e Fácil</h3>
                <p>Crie seu site em minutos com nosso editor intuitivo</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Responsivo</h3>
                <p>Seu site perfeito em qualquer dispositivo</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Seguro</h3>
                <p>SSL gratuito e proteção contra ameaças</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>Suporte 24/7</h3>
                <p>Equipe sempre pronta para ajudar</p>
            </div>
        </div>
    </div>
</section>

<section class="cta">
    <div class="container">
        <h2>Pronto para começar?</h2>
        <p>Junte-se a milhares de empresas que já confiam em nós</p>
        <a href="cadastro.php" class="btn btn-primary btn-large">Criar Minha Conta Grátis</a>
    </div>
</section>

<style>
.hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 100px 0;
    text-align: center;
}

.hero-content h1 {
    font-size: 48px;
    margin-bottom: 20px;
}

.hero-content p {
    font-size: 20px;
    margin-bottom: 40px;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-large {
    padding: 15px 40px;
    font-size: 18px;
}

.features {
    padding: 80px 0;
    background: #f8f9fa;
}

.features h2 {
    text-align: center;
    font-size: 36px;
    margin-bottom: 60px;
    color: #2c3e50;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 32px;
    color: white;
}

.feature-card h3 {
    margin-bottom: 15px;
    color: #2c3e50;
}

.feature-card p {
    color: #666;
    line-height: 1.6;
}

.cta {
    background: #2c3e50;
    color: white;
    padding: 80px 0;
    text-align: center;
}

.cta h2 {
    font-size: 36px;
    margin-bottom: 20px;
}

.cta p {
    font-size: 18px;
    margin-bottom: 40px;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 32px;
    }
    
    .hero-content p {
        font-size: 16px;
    }
    
    .features h2,
    .cta h2 {
        font-size: 28px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>