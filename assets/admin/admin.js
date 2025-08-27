/**
 * Squidge Admin JavaScript
 *
 * @package Squidge
 * @version 0.1.1
 */

(function ($) {
  'use strict';

  // Inicializar quando o DOM estiver pronto
  $(document).ready(function () {
    console.log('Squidge Admin JS carregado com sucesso!');

    // Inicializar funcionalidades básicas
    initSquidgeAdmin();
  });

  /**
   * Inicializar funcionalidades administrativas
   */
  function initSquidgeAdmin() {
    // Adicionar classes CSS para melhorar a aparência
    $('.squidge-dashboard').addClass('squidge-loaded');

    // Inicializar tooltips se existirem
    if ($.fn.tooltip) {
      $('[data-toggle="tooltip"]').tooltip();
    }

    // Inicializar modais se existirem
    if ($.fn.modal) {
      $('[data-toggle="modal"]').modal();
    }
  }

  /**
   * Função para mostrar notificações
   */
  function showNotification(message, type = 'info') {
    const notification = $('<div>')
      .addClass('squidge-notification')
      .addClass('squidge-notification-' + type)
      .text(message)
      .appendTo('body');

    // Auto-remover após 5 segundos
    setTimeout(function () {
      notification.fadeOut(function () {
        $(this).remove();
      });
    }, 5000);
  }

  /**
   * Função para atualizar estatísticas
   */
  function updateStats() {
    // Placeholder para futuras implementações
    console.log('Atualizando estatísticas...');
  }

  // Expor funções globalmente se necessário
  window.SquidgeAdmin = {
    showNotification: showNotification,
    updateStats: updateStats
  };

})(jQuery);
