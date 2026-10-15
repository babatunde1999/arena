<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';

logout();

redirect(SITE_URL . '/index.php');