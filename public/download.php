<?php

chdir(dirname(__DIR__));

exec('rm -rf /tmp/technical-assessment.zip');
exec('git archive -o /tmp/technical-assessment.zip 87c154adf6d033f1d856cb70f6611e789d54d17c');

// download the zip file
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="technical-assessment.zip"');
readfile('/tmp/technical-assessment.zip');