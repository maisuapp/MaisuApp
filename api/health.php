<?php
header('Content-Type: text/plain; charset=utf-8');
echo "MaisuApp PHP Serverless OK\n";
echo "PHP Version: " . phpversion() . "\n";
echo "OS: " . PHP_OS . "\n";
echo "Loaded Extensions: " . implode(', ', get_loaded_extensions()) . "\n";
