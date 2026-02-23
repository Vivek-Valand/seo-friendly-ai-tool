<?php
// Ensure the /tmp directory exists for Laravel views
if (!is_dir('/tmp/views')) {
    mkdir('/tmp/views', 0755, true);
}

require __DIR__ . '/../public/index.php';