<?php

return [

    /*
     *
     * Traductions partagées.
     *
     */
    'title' => 'Installation E-menu',
    'next' => 'Étape Suivante',
    'back' => 'Précédent',
    'finish' => 'Installer',
    'forms' => [
        'errorTitle' => 'Les erreurs suivantes se sont produites :',
    ],

    /*
     *
     * Traductions de la page d'accueil.
     *
     */
    'welcome' => [
        'templateTitle' => 'Bienvenue dans E-menu',
        'title'   => 'Installation E-menu',
        'message' => 'Système de Menu Numérique Moderne avec Intégration CinetPay.',
        'next'    => 'Commencer l\'Installation',
    ],

    /*
     *
     * Traductions de la page des prérequis.
     *
     */
    'requirements' => [
        'templateTitle' => 'Étape 1 | Prérequis Serveur',
        'title' => 'Prérequis Serveur',
        'next'    => 'Vérifier les Permissions',
    ],

    /*
     *
     * Traductions de la page des permissions.
     *
     */
    'permissions' => [
        'templateTitle' => 'Étape 2 | Permissions',
        'title' => 'Permissions',
        'next' => 'Configurer l\'Environnement',
    ],

    /*
     *
     * Traductions de la page d'environnement.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Étape 3 | Paramètres d\'Environnement',
            'title' => 'Paramètres d\'Environnement',
            'desc' => 'Veuillez sélectionner comment vous souhaitez configurer le fichier <code>.env</code> des applications.',
            'wizard-button' => 'Configuration par Assistant',
            'classic-button' => 'Éditeur de Texte Classique',
        ],
        'wizard' => [
            'templateTitle' => 'Étape 3 | Paramètres d\'Environnement | Assistant Guidé',
            'title' => 'Assistant <code>.env</code> Guidé',
            'tabs' => [
                'environment' => 'Environnement',
                'database' => 'Base de Données',
                'application' => 'Application',
            ],
            'form' => [
                'name_required' => 'Un nom d\'environnement est requis.',
                'app_name_label' => 'Nom de l\'Application',
                'app_name_placeholder' => 'Nom de l\'Application',
                'app_environment_label' => 'Environnement de l\'Application',
                'app_environment_label_local' => 'Local',
                'app_environment_label_developement' => 'Développement',
                'app_environment_label_qa' => 'QA',
                'app_environment_label_production' => 'Production',
                'app_environment_label_other' => 'Autre',
                'app_environment_placeholder_other' => 'Entrez votre environnement...',
                'app_debug_label' => 'Debug de l\'Application',
                'app_debug_label_true' => 'Vrai',
                'app_debug_label_false' => 'Faux',
                'app_log_level_label' => 'Niveau de Log',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'URL de l\'Application',
                'app_url_placeholder' => 'URL de l\'Application',
                'db_connection_failed' => 'Impossible de se connecter à la base de données.',
                'db_connection_label' => 'Connexion Base de Données',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Hôte Base de Données',
                'db_host_placeholder' => 'Hôte Base de Données',
                'db_port_label' => 'Port Base de Données',
                'db_port_placeholder' => 'Port Base de Données',
                'db_name_label' => 'Nom Base de Données',
                'db_name_placeholder' => 'Nom Base de Données',
                'db_username_label' => 'Nom d\'Utilisateur Base de Données',
                'db_username_placeholder' => 'Nom d\'Utilisateur Base de Données',
                'db_password_label' => 'Mot de Passe Base de Données',
                'db_password_placeholder' => 'Mot de Passe Base de Données',

                'app_tabs' => [
                    'more_info' => 'Plus d\'Informations',
                    'broadcasting_title' => 'Diffusion',
                    'broadcasting_label' => 'Pilote de Diffusion',
                    'broadcasting_placeholder' => 'Pilote de Diffusion',
                    'cache_label' => 'Pilote de Cache',
                    'cache_placeholder' => 'Pilote de Cache',
                    'session_label' => 'Pilote de Session',
                    'session_placeholder' => 'Pilote de Session',
                    'queue_label' => 'Pilote de File d\'Attente',
                    'queue_placeholder' => 'Pilote de File d\'Attente',
                    'redis_label' => 'Pilote Redis',
                    'redis_host' => 'Hôte Redis',
                    'redis_password' => 'Mot de Passe Redis',
                    'redis_port' => 'Port Redis',

                    'mail_label' => 'Mail',
                    'mail_driver_label' => 'Pilote Mail',
                    'mail_driver_placeholder' => 'Pilote Mail',
                    'mail_host_label' => 'Hôte Mail',
                    'mail_host_placeholder' => 'Hôte Mail',
                    'mail_port_label' => 'Port Mail',
                    'mail_port_placeholder' => 'Port Mail',
                    'mail_username_label' => 'Nom d\'Utilisateur Mail',
                    'mail_username_placeholder' => 'Nom d\'Utilisateur Mail',
                    'mail_password_label' => 'Mot de Passe Mail',
                    'mail_password_placeholder' => 'Mot de Passe Mail',
                    'mail_encryption_label' => 'Chiffrement Mail',
                    'mail_encryption_placeholder' => 'Chiffrement Mail',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'ID App Pusher',
                    'pusher_app_id_palceholder' => 'ID App Pusher',
                    'pusher_app_key_label' => 'Clé App Pusher',
                    'pusher_app_key_palceholder' => 'Clé App Pusher',
                    'pusher_app_secret_label' => 'Secret App Pusher',
                    'pusher_app_secret_palceholder' => 'Secret App Pusher',
                ],
                'buttons' => [
                    'setup_database' => 'Configurer Base de Données',
                    'setup_application' => 'Configurer Application',
                    'install' => 'Installer',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Étape 3 | Paramètres d\'Environnement | Éditeur Classique',
            'title' => 'Éditeur d\'Environnement Classique',
            'save' => 'Sauvegarder .env',
            'back' => 'Utiliser l\'Assistant de Formulaire',
            'install' => 'Sauvegarder et Installer',
        ],
        'success' => 'Vos paramètres de fichier .env ont été sauvegardés.',
        'errors' => 'Impossible de sauvegarder le fichier .env, Veuillez le créer manuellement.',
    ],

    'install' => 'Installer',

    /*
     *
     * Traductions de l'URL d'installation.
     *
     */
    'installed' => [
        'success_log_message' => 'Installateur Laravel installé avec succès le ',
    ],

    /*
     *
     * Traductions de la page finale.
     *
     */
    'final' => [
        'title' => 'Installation Terminée',
        'templateTitle' => 'Installation Terminée',
        'finished' => 'L\'application a été installée avec succès.',
        'migration' => 'Sortie de Migration et Seed :',
        'console' => 'Sortie Console d\'Application :',
        'log' => 'Entrée Log d\'Installation :',
        'env' => 'Fichier .env Final :',
        'exit' => 'Cliquer ici pour sortir',
    ],

    /*
     *
     * Traductions des pages de mise à jour.
     *
     */
    'updater' => [
        /*
         *
         * Traductions de la page d'accueil de mise à jour.
         *
         */
        'welcome' => [
            'title'   => 'Bienvenue dans le Mise à Jour Laravel',
            'message' => 'Bienvenue dans l\'assistant de mise à jour.',
        ],

        /*
         *
         * Traductions de la page d'aperçu de mise à jour.
         *
         */
        'overview' => [
            'title'   => 'Aperçu',
            'message' => 'Il y a 1 mise à jour.|Il y a :number mises à jour.',
            'install_updates' => 'Installer les Mises à Jour',
        ],

        /*
         *
         * Traductions de la page finale de mise à jour.
         *
         */
        'final' => [
            'title' => 'Terminé',
            'finished' => 'La base de données de l\'application a été mise à jour avec succès.',
            'exit' => 'Cliquer ici pour sortir',
        ],

        'log' => [
            'success_message' => 'Installateur Laravel mis à jour avec succès le ',
        ],
    ],

    /*
     *
     * Traductions pour la gestion des médias.
     *
     */
    'media_management' => 'Gestion des Médias',
    'add_media' => 'Ajouter un Média',
    'upload_media' => 'Télécharger un Média',
    'select_image' => 'Sélectionner une Image',
    'supported_formats' => 'Formats supportés: JPEG, PNG, JPG, GIF, SVG (max 2MB)',
    'cancel' => 'Annuler',
    'upload' => 'Télécharger',
    'uploading' => 'Téléchargement...',
    'no_media' => 'Aucun média trouvé',
    'upload_first_media' => 'Téléchargez votre premier média pour commencer.',
    'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ce média ?',
    'upload_error' => 'Erreur lors du téléchargement. Veuillez réessayer.',
];
