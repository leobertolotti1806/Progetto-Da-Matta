<?php

// RSAService
require_once dirname(__FILE__) . '/src/RSAService/RSAServiceContract.php';
require_once dirname(__FILE__) . '/src/RSAService/OpenSSL_RSAService.php';
require_once dirname(__FILE__) . '/src/RSAService/SeclibRSAService.php';

require_once dirname(__FILE__) . '/src/ApiAuthentication.php';
require_once dirname(__FILE__) . '/src/Api.php';
require_once dirname(__FILE__) . '/src/Payment.php';
require_once dirname(__FILE__) . '/src/Request.php';
require_once dirname(__FILE__) . '/src/RSAServiceFactory.php';

use SatispayGBusiness\Api;

$myKeys = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/secure/satispay.key"));

Api::setPrivateKey($myKeys->privateKey);
Api::setPublicKey($myKeys->publicKey);
Api::setKeyId($myKeys->keyId);
Api::setEnv("production");