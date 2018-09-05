# Bitsand ECS production docker container

The entrypoint will be `docker-config/ecs/ssm-entrypoint.sh` running from `/ssm-entrypoint.sh` in the container

The terms file will be loaded from `docker-config/ecs/bitsand/terms_ENV['BITSAND_SYSTEM_NAME'].php`

The bitsand config file will be `docker-config/ecs/bitsand/inc/inc_config.php`

A new file with secrets will be created inside the container at `/secrets/bitsand-secrets.php`, this will be required by
the bitsand config

For extra security at the end of the entrypoint the following things will happen:

1. The `/var/www/html/docker-config` directory will be deleted
2. The following env vars will be unset:
   1. BITSAND_ROOT_USER_ID
   2. BITSAND_DB_HOST
   3. BITSAND_DB_NAME
   4. BITSAND_DB_USER
   5. SSM_AWS_REGION
   6. SSM_KEY_BITSAND_DB_PASS
   7. SSM_KEY_BITSAND_ENCRYPTION_KEY
   8. SSM_KEY_BITSAND_PW_SALT
   9. BITSAND_INSTALL_MODE

## Environment variables

The following table lists the environment variables you can set, what their default is, whether they are required, and
whether they will exist once the entrypoint has been run, (_note_: If there is no default you MUST define this):

| Env Var Name                   | Default   | Exist after entrypoint | Description                                                        |
| ------------------------------ | --------- |:----------------------:| ------------------------------------------------------------------ |
| BITSAND_SYSTEM_NAME            |           | Yes                    | Decides which terms file to load                                   |
| BITSAND_INSTALL_MODE           | FALSE     | No                     | Unless set to TRUE install and NON_WEB directories will be removed |
| BITSAND_ROOT_USER_ID           | 1         | No                     |                                                                    |
| BITSAND_DB_HOST                |           | No                     |                                                                    |
| BITSAND_DB_NAME                |           | No                     |                                                                    |
| BITSAND_DB_USER                |           | No                     |                                                                    |
| SSM_AWS_REGION                 | us-east-1 | No                     | Region in which to query SSM                                       |
| SSM_KEY_BITSAND_DB_PASS        |           | No                     | SSM Key to load database password from                             |
| SSM_KEY_BITSAND_ENCRYPTION_KEY |           | No                     | SSM Key to load encryption key from                                |
| SSM_KEY_BITSAND_PW_SALT        |           | No                     | SSM Key to load password salt from                                 |
| BITSAND_SYSTEM_URL             |           | Yes                    |                                                                    |
| BITSAND_DEBUG_MODE             | FALSE     | Yes                    |                                                                    |
| BITSAND_MAINTAINENCE_MODE      | FALSE     | Yes                    |                                                                    |
| BITSAND_LOG_WARNINGS           | TRUE      | Yes                    |                                                                    |
| BITSAND_LOG_ERRORS             | TRUE      | Yes                    |                                                                    |


## Entrypoint

The file ssm-entrypoint.sh will do the following things

1. Load DB\_PASSWORD, ENCRYPTION\_KEY, and PW\_SALT from SSM
2. The entrypoint will make a new file `/secrets/bitsand.php` and populate PHP constants:
   1. ROOT_USER_ID - From BITSAND_ROOT_USER_ID env var
   2. DB_HOST - From BITSAND_DB_HOST env var
   3. DB_NAME - From BITSAND_DB_NAME env var
   4. DB_USER - From BITSAND_DB_USER env var
   6. DB_PASS - From value of SSM param with key specified in SSM_KEY_BITSAND_DB_PASS env var
   7. CRYPT_KEY - From value of SSM param with key specified in SSM_KEY_BITSAND_ENCRYPTION_KEY env var
   8. PW_SALT - From value of SSM param with key specified in SSM_KEY_BITSAND_PW_SALT env var
3. Copy `docker-config/ecs/bitsand/terms_$BITSAND_SYSTEM_NAME.php` to `/var/www/html/terms.php`
4. Unless `BIT_SAND_INSTALL_MODE` is set to `TRUE` will recursively delete `/var/www/html/NON_WEB` and `/var/www/html/install`
5. Unset some env vars (see above)
6. Delete `/var/www/html/docker-config` recursively

