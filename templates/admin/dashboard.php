<?php

/**
 * Dashboard Principal do Squidge
 *
 * @package Squidge
 * @version 1.0.0
 */

// Prevenir acesso direto
if (!defined('ABSPATH')) {
  exit;
}
?>

<div class="wrap">
    <h1>Squidge Dashboard</h1>
    
    <div class="squidge-dashboard">
        <div class="squidge-stats-grid">
            <div class="squidge-stat-card">
                <h3>Imagens Otimizadas</h3>
                <div class="stat-number">0</div>
                <p>Total de imagens processadas</p>
            </div>
            
            <div class="squidge-stat-card">
                <h3>Espaço Economizado</h3>
                <div class="stat-number">0 MB</div>
                <p>Total de espaço economizado</p>
            </div>
            
            <div class="squidge-stat-card">
                <h3>Taxa de Sucesso</h3>
                <div class="stat-number">0%</div>
                <p>Taxa de otimização bem-sucedida</p>
            </div>
        </div>
        
        <div class="squidge-actions">
            <h2>Ações Rápidas</h2>
            <a href="<?php echo admin_url('admin.php?page=squidge-batch-optimizer'); ?>" class="button button-primary">
                Otimizar Imagens em Lote
            </a>
            <a href="<?php echo admin_url('admin.php?page=squidge-statistics'); ?>" class="button button-secondary">
                Ver Estatísticas Detalhadas
            </a>
        </div>
        
        <div class="squidge-status">
            <h2>Status das Ferramentas</h2>
            <ul>
                <li><strong>JPEGOptim:</strong> <span class="status-ok">✓ Disponível</span></li>
                <li><strong>OptiPNG:</strong> <span class="status-ok">✓ Disponível</span></li>
                <li><strong>cwebp:</strong> <span class="status-ok">✓ Disponível</span></li>
                <li><strong>AVIF:</strong> <span class="status-ok">✓ Disponível</span></li>
            </ul>
        </div>
    </div>
</div>

<style>
.squidge-dashboard {
    margin-top: 20px;
}

.squidge-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.squidge-stat-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.squidge-stat-card h3 {
    margin: 0 0 10px 0;
    color: #23282d;
}

.stat-number {
    font-size: 2.5em;
    font-weight: bold;
    color: #0073aa;
    margin: 10px 0;
}

.squidge-actions {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.squidge-actions h2 {
    margin-top: 0;
}

.squidge-actions .button {
    margin-right: 10px;
}

.squidge-status {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.squidge-status h2 {
    margin-top: 0;
}

.squidge-status ul {
    list-style: none;
    padding: 0;
}

.squidge-status li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.squidge-status li:last-child {
    border-bottom: none;
}

.status-ok {
    color: #46b450;
    font-weight: bold;
}
</style>
