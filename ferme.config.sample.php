<?php

$config = array(
    'db_host' => 'localhost',
    'db_name' => 'XXXXXXXXXX',
    'db_user' => 'XXXXXXXXXX',
    'db_password' => 'XXXXXXXXXX',
    'base_url' => 'http://localhost/ferme/',
    'source' => 'yeswiki-cercopitheque-20200605',
    'log_file' => 'ferme.log',
    'ferme_path' => 'wikis/',
    'archives_path' => 'archives/',
    'tmp_path' => '/tmp/',
    'template' => 'default',
    'mail_from' => 'no-reply@domain.tld',
    'welcome_text' => "<h1>Bienvenue</h1> ! <p> Un texte de description.",
    'users' => array(
        // Les mots de passe doivent être chiffré
        'admin' => password_hash('password', PASSWORD_DEFAULT),
    ),
    'contacts' => array(
        'Votre nom' => 'votre.nom@domain.tld'
    ),
    // Default md5 password for installed wikis
    'admin_password' => md5('password'),
);
