<?php
echo '<h1>Server PHP Info</h1>';
echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
echo '<p><strong>PHP Binary:</strong> ' . PHP_BINARY . '</p>';
echo '<p><strong>SAPI:</strong> ' . php_sapi_name() . '</p>';
echo '<p><strong>Loaded php.ini:</strong> ' . php_ini_loaded_file() . '</p>';
echo '<hr>';
echo '<p><strong>PHP 8.4+ Property Hooks Support:</strong> ';
echo version_compare(PHP_VERSION, '8.4.0', '>=') ? '✅ YES' : '❌ NO (THIS IS THE PROBLEM)';
echo '</p>';
