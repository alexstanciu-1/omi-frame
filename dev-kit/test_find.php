<?php

echo "<pre>NOT ONE ERROR!<br/>\n";

$cmd = 'find "/mnt/d/Work_2020/TRAVEL/ECOMM/TF_ecomm - 2023-02-23 BETA - DEV" '.
							'-type f -not -path \'*/\.git/*\' -not -path \'*/~gens/*\' -not -path \'*/temp/*\' '.
							'-printf \'%s %T@ %p\n\' '.
							# '-newermt \'1990-04-01 00:00:00\' '.
							'';

echo $cmd, "\n";

$t0 = microtime(true);
echo $out = shell_exec($cmd);
$t1 = microtime(true);

qvar_dumpk($t1 - $t0, count(preg_split("/(\\s*\\r?\\n\\s*)/uis", $out)));

$rc = file_put_contents("/mnt/d/test_ubuntu_2.txt", "test_ubuntu ....");
var_dump($rc);
ob_start();
# die;
