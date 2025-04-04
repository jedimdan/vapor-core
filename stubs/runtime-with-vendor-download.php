<?php

ini_set('display_errors', '1');

error_reporting(E_ALL);

$appRoot = $_ENV['LAMBDA_TASK_ROOT'];

/*
|--------------------------------------------------------------------------
| Download The Vendor Directory
|--------------------------------------------------------------------------
|
| For applications which are loading their vendor directory on container
| boot, we'll need to download it and extract it into place. This can
| allow larger applications to be uploaded to Vapor without issues.
|
*/

fwrite(STDERR, 'Downloading the application vendor archive...');

exec(sprintf('/opt/awscli/aws s3 cp s3://%s/%s-vendor.zip /tmp/vendor.zip',
    $_ENV['VAPOR_ARTIFACT_BUCKET_NAME'],
    $_ENV['VAPOR_ARTIFACT_NAME']
));

$zip = new ZipArchive;
$zip->open('/tmp/vendor.zip');
$zip->extractTo('/tmp/vendor');
$zip->close();

unlink('/tmp/vendor.zip');

/*
|--------------------------------------------------------------------------
| Bootstrap The Runtime
|--------------------------------------------------------------------------
|
| If the application is being served by the console layer, we will require in the
| console runtime. Otherwise, we will use the FPM runtime. Vapor will setup an
| environment variable for the console layer that we will use to check this.
|
*/

require '/tmp/vendor/autoload.php';

if (isset($_ENV['APP_RUNNING_IN_CONSOLE']) &&
    $_ENV['APP_RUNNING_IN_CONSOLE'] === 'true') {
    return require __DIR__.'/cliRuntime.php';
} else {
    return require __DIR__.'/fpmRuntime.php';
}
