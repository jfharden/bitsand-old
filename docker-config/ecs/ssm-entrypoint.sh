#!/bin/bash

set -e

echo "Loading secrets from SSM"

BITSAND_DB_PASS=`aws ssm get-parameter --region $SSM_AWS_REGION --name $SSM_KEY_BITSAND_DB_PASS --with-decryption --output text --query "Parameter.Value"`
if [ $? -ne 0 ]; then
  echo "Failed to lookup BITSAND_DB_PASS key in SSM key $SSM_KEY_BITSAND_DB_PASS"
  exit 3
fi

echo "Loaded $SSM_KEY_BITSAND_DB_PASS"

BITSAND_ENCRYPTION_KEY=`aws ssm get-parameter --region $SSM_AWS_REGION --name $SSM_KEY_BITSAND_ENCRYPTION_KEY --with-decryption --output text --query "Parameter.Value"`
if [ $? -ne 0 ]; then
  echo "Failed to lookup BITSAND_ENCRYPTION_KEY  key in SSM key $SSM_KEY_BITSAND_ENCRYPTION_KEY"
  exit 3
fi

echo "Loaded $SSM_KEY_BITSAND_ENCRYPTION_KEY"

BITSAND_PW_SALT=`aws ssm get-parameter --region $SSM_AWS_REGION --name $SSM_KEY_BITSAND_PW_SALT --with-decryption --output text --query "Parameter.Value"`
if [ $? -ne 0 ]; then
  echo "Failed to lookup BITSAND_PW_SALT key in SSM key $SSM_KEY_BITSAND_PW_SALT"
  exit 3
fi

echo "Loaded $SSM_KEY_BITSAND_PW_SALT"

echo "<?php
define ('ROOT_USER_ID', '$BITSAND_ROOT_USER_ID');
define ('DB_HOST', '$BITSAND_DB_HOST');
define ('DB_NAME', '$BITSAND_DB_NAME');
define ('DB_USER', '$BITSAND_DB_USER');
define ('DB_PASS', '$BITSAND_DB_PASS');
define ('CRYPT_KEY', '$BITSAND_ENCRYPTION_KEY');
define ('PW_SALT', '$BITSAND_PW_SALT');
?>" > /secrets/bitsand.php

chmod 440 /secrets/bitsand.php
chown root:www-data /secrets/bitsand.php

echo "Created /secrets/bitsand.php"

cp /var/www/html/docker-config/ecs/bitsand/terms_${BITSAND_SYSTEM_NAME}.php /var/www/html/terms.php

echo "Copied terms file /var/www/html/docker-config/ecs/bitsand/terms_${BITSAND_SYSTEM_NAME}.php to /var/www/html/terms.php"

if [ "$BITSAND_INSTALL_MODE" == "TRUE" ]; then
  echo "In install mode, not removing any directories"
else
  echo "Install mode not TRUE, removing /var/www/html/NON_WEB and /var/www/html/install"
  rm -rf /var/www/html/NON_WEB
  rm -rf /var/www/html/install
fi

echo "Unsetting env vars"

unset BITSAND_ROOT_USER_ID
unset BITSAND_DB_HOST
unset BITSAND_DB_NAME
unset BITSAND_DB_USER
unset BITSAND_DB_PASS
unset BITSAND_ENCRYPTION_KEY
unset BITSAND_PW_SALT
unset SSM_AWS_REGION
unset SSM_KEY_BITSAND_DB_PASS
unset SSM_KEY_BITSAND_ENCRYPTION_KEY
unset SSM_KEY_BITSAND_PW_SALT

echo "Removing /var/www/html/docker-config"

rm -rf /var/www/html/docker-config

echo "SSM entrypoint setup complete, running docker-php-entrypoint"

exec "/usr/local/bin/docker-php-entrypoint" "$@"
