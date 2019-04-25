<?php

$date = date("Y-m-d H:i:s");
$adminName = 'WikiAdmin';
$adminPassword = $this->fermeConfig['admin_password'];
$adminEmail = $mail;


$listQuery = array();
$listQuery[] = array(
    'params' => array(),
    'query' => "CREATE TABLE `${prefix}pages` (
        id int(10) unsigned NOT NULL auto_increment,
        tag varchar(50) NOT NULL default '',
        time datetime NOT NULL,
        body longtext NOT NULL,
        body_r text,
        owner varchar(50) NOT NULL default '',
        user varchar(50) NOT NULL default '',
        latest enum('Y','N') NOT NULL default 'N',
        handler varchar(30) NOT NULL default 'page',
        comment_on varchar(50) NOT NULL default '',
        PRIMARY KEY  (id),
        KEY idx_tag (tag), KEY idx_time (time),
        KEY idx_latest (latest),
        KEY idx_comment_on (comment_on)
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
);


$listQuery[] = array(
    'params' => array(),
    'query' => "CREATE TABLE `${prefix}acls` (
          page_tag varchar(50) NOT NULL default '',
          privilege varchar(20) NOT NULL default '',
          list text NOT NULL,
          PRIMARY KEY  (page_tag, privilege)
      ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
);

$listQuery[] = array(
    'params' => array(),
    'query' => "CREATE TABLE `${prefix}links` (
        from_tag char(50) NOT NULL default '',
        to_tag char(50) NOT NULL default '',
        UNIQUE KEY from_tag (from_tag,to_tag),
        KEY idx_from (from_tag),
        KEY idx_to (to_tag)
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
);

$listQuery[] = array(
    'params' => array(),
    'query' => "CREATE TABLE `${prefix}users` (
        name varchar(80) NOT NULL default '',
        password varchar(32) NOT NULL default '',
        email varchar(50) NOT NULL default '',
        motto text,
        revisioncount int(10) unsigned NOT NULL default '20',
        changescount int(10) unsigned NOT NULL default '50',
        doubleclickedit enum('Y','N') NOT NULL default 'Y',
        signuptime datetime NOT NULL,
        show_comments enum('Y','N') NOT NULL default 'N',
        PRIMARY KEY  (name),
        KEY idx_name (name),
        KEY idx_signuptime (signuptime)
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
);

$listQuery[] = array(
    'params' => array(),
    'query' => "CREATE TABLE `${prefix}triples` (
        `id` int(10) unsigned NOT NULL auto_increment,
        `resource` varchar(191) NOT NULL default '',
        `property` varchar(191) NOT NULL default '',
        `value` text NOT NULL,
        PRIMARY KEY  (`id`),
        KEY `resource` (`resource`),
        KEY `property` (`property`)
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
);

$listQuery[] = array(
    'params' => array(),
    'query' => "CREATE TABLE `${prefix}referrers` (
        page_tag char(50) NOT NULL default '',
        referrer char(150) NOT NULL default '',
        time datetime NOT NULL,
        KEY idx_page_tag (page_tag),
        KEY idx_time (time)
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
);

$listQuery[] = array(
    'params' => array(
      ':adminName' => $adminName,
      ':adminEmail' => $adminEmail,
      ':adminPassword' => $adminPassword
    ),
    'query' => "INSERT INTO ${prefix}users (signuptime, name, email, motto, password)
        VALUES (now(), :adminName, :adminEmail, '', :adminPassword);"
);

$listQuery[] = array(
    'params' => array(':adminName' => $adminName),
    'query' => "INSERT INTO ${prefix}triples (id, resource, property, value)
        VALUES ('1', 'ThisWikiGroup:admins', 'http://www.wikini.net/_vocabulary/acls', :adminName);",
);


$setupDocDir = 'packages/' . $this->fermeConfig['source'] . '/pages' ;
$d = dir($setupDocDir);
while ($doc = $d->read()) {
    if (is_dir($setupDocDir.$doc) || substr($doc, -4) != '.txt') {
        continue;
    }
    $pageContent = str_replace('{{root_page}}', 'PagePrincipale', implode('', file("$setupDocDir/$doc")));
    if ($doc == '_root_page.txt') {
        $pagename = 'PagePrincipale';
    } else {
        $pagename = substr($doc, 0, strpos($doc, '.txt'));
    }

    // TODO : tester si la page existe deja ? En vrai cela ne devrait pas arriver.
    $listQuery[] = array(
        'params' => array(
            ':pageName' => $pagename,
            ':adminName' => $adminName,
            ':pageContent' => $pageContent,
        ),
        'query' => "INSERT INTO ${prefix}pages (tag, body, body_r, user, owner, time, latest)
            VALUES (:pageName, :pageContent, '', :adminName, :adminName, now(), 'Y');"
  );
}
