<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Messages de Notifications RestroSaaS
    |--------------------------------------------------------------------------
    */

    // Types de notifications
    'system_alert' => 'Alerte Système',
    'performance_warning' => 'Avertissement Performance',
    'security_incident' => 'Incident de Sécurité',
    'backup_status' => 'État de Sauvegarde',
    'order_critical' => 'Commande Critique',
    'restaurant_offline' => 'Restaurant Hors Ligne',
    'payment_failure' => 'Échec de Paiement',
    'training_reminder' => 'Rappel de Formation',
    'maintenance_scheduled' => 'Maintenance Programmée',

    // Niveaux de priorité
    'priority_low' => 'Priorité Faible',
    'priority_normal' => 'Priorité Normale',
    'priority_high' => 'Priorité Élevée',
    'priority_critical' => 'Priorité Critique',
    'priority_emergency' => 'Urgence',

    // Canaux de notification
    'channel_email' => 'Email',
    'channel_sms' => 'SMS',
    'channel_push' => 'Notification Push',
    'channel_slack' => 'Slack',
    'channel_webhook' => 'Webhook',
    'channel_database' => 'Base de Données',

    // Messages système
    'notification_sent_successfully' => 'Notification envoyée avec succès',
    'notification_failed' => 'Échec d\'envoi de la notification',
    'all_channels_tested' => 'Tous les canaux testés',
    'test_completed' => 'Test terminé',
    'no_notifications_found' => 'Aucune notification trouvée',
    'notification_cleared' => 'Notifications supprimées',

    // Alertes automatiques
    'system_overload' => 'Système surchargé - Performance dégradée',
    'backup_failed_alert' => 'Échec de sauvegarde automatique',
    'security_breach_detected' => 'Tentative d\'intrusion détectée',
    'restaurant_went_offline' => 'Restaurant passé hors ligne',
    'payment_processing_error' => 'Erreur de traitement des paiements',
    'high_response_time' => 'Temps de réponse élevé détecté',

    // Messages de formation
    'training_session_started' => 'Session de formation démarrée',
    'training_completed' => 'Formation terminée avec succès',
    'certificate_generated' => 'Certificat généré',
    'quiz_passed' => 'Quiz réussi',
    'quiz_failed' => 'Quiz échoué - révision nécessaire',

    // Messages de maintenance
    'maintenance_starting' => 'Début de maintenance programmée',
    'maintenance_completed' => 'Maintenance terminée',
    'system_updated' => 'Système mis à jour',
    'backup_restored' => 'Sauvegarde restaurée avec succès',

    // Statuts
    'status_sent' => 'Envoyée',
    'status_pending' => 'En attente',
    'status_failed' => 'Échec',
    'status_delivered' => 'Livrée',
    'status_read' => 'Lue',

    // Actions
    'mark_as_read' => 'Marquer comme lue',
    'mark_all_read' => 'Tout marquer comme lu',
    'delete_notification' => 'Supprimer la notification',
    'resend_notification' => 'Renvoyer la notification',
    'test_notification' => 'Tester la notification',

    // Erreurs communes
    'email_config_missing' => 'Configuration email manquante',
    'sms_config_missing' => 'Configuration SMS manquante',
    'slack_webhook_missing' => 'Webhook Slack non configuré',
    'webhook_url_invalid' => 'URL webhook invalide',
    'notification_limit_reached' => 'Limite de notifications atteinte',

    // Templates d\'emails
    'email_subject_critical' => '[CRITIQUE] RestroSaaS - Action Immédiate Requise',
    'email_subject_high' => '[URGENT] RestroSaaS - Attention Nécessaire',
    'email_subject_normal' => '[INFO] RestroSaaS - Notification',
    'email_subject_low' => '[LOG] RestroSaaS - Information',

    // Formats de message
    'sms_format' => 'RestroSaaS: %s [%s]',
    'slack_format' => ':warning: *RestroSaaS* - %s',
    'push_title' => 'RestroSaaS Notification',

    // Rapports
    'notification_stats' => 'Statistiques des Notifications',
    'total_sent_today' => 'Total envoyées aujourd\'hui',
    'success_rate' => 'Taux de réussite',
    'most_used_channel' => 'Canal le plus utilisé',
    'average_response_time' => 'Temps de réponse moyen',
];
