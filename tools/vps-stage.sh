set -euo pipefail
umask 077
BASE=/opt/apps/projects/minikids
STAGE=/root/minikeds-dev2-deploy
RELEASE=$BASE/releases/toycommerce-20261020
test ! -e "$RELEASE"
mkdir -p "$RELEASE" "$BASE/releases"
chmod 755 "$BASE/releases" "$RELEASE"
tar -xzf "$STAGE/runtime.tar.gz" -C "$RELEASE" --no-same-owner
cp "$STAGE/minikids-estetica.php" "$STAGE/minikids-catalogo.js" "$RELEASE/wp-content/mu-plugins/"
python3 - <<'PY'
import json,secrets,subprocess,pathlib
base=pathlib.Path('/opt/apps/projects/minikids')
private=base/'private';private.mkdir(exist_ok=True,mode=0o700)
password=secrets.token_hex(32)
sql="CREATE DATABASE minikeds_dev2_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;CREATE USER 'minikeds2_app'@'localhost' IDENTIFIED BY '"+password+"';GRANT ALL ON minikeds_dev2_prod.* TO 'minikeds2_app'@'localhost';"
subprocess.run(['mysql'],input=sql.encode(),check=True)
config={'DB_NAME':'minikeds_dev2_prod','DB_USER':'minikeds2_app','DB_PASSWORD':password,'DB_HOST':'localhost','DB_CHARSET':'utf8mb4','DB_COLLATE':'','WP_HOME':'https://minikids.vitorstream.shop','WP_SITEURL':'https://minikids.vitorstream.shop','WP_ENVIRONMENT_TYPE':'production','WP_DEBUG':False,'WP_DEBUG_DISPLAY':False,'DISALLOW_FILE_EDIT':True,'DISABLE_WP_CRON':True,'FORCE_SSL_ADMIN':True,'WP_MEMORY_LIMIT':'256M','WP_MAX_MEMORY_LIMIT':'384M','MINIKIDS_BREVO_CONFIG':str(base/'mail-private/brevo.json')}
for name in ['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT']:config[name]=secrets.token_hex(48)
(private/'database-dev2.json').write_text(json.dumps(config));(private/'database-dev2.json').chmod(0o600)
runtime=base/'runtime-private';runtime.mkdir(exist_ok=True,mode=0o750)
runtime.chmod(0o750)
subprocess.run(['chown','root:minikids',str(runtime)],check=True)
(runtime/'config.json').write_text(json.dumps(config));(runtime/'config.json').chmod(0o640)
subprocess.run(['chown','root:minikids',str(runtime/'config.json')],check=True)
mail=base/'mail-private';mail.mkdir(exist_ok=True,mode=0o700)
import shutil
shutil.copyfile('/etc/minikids/brevo.json',mail/'brevo.json')
(mail/'brevo.json').chmod(0o600)
subprocess.run(['chown','-R','minikids:minikids',str(mail)],check=True)
root=base/'releases/toycommerce-20261020'
(root/'wp-config.php').write_text("<?php\nforeach(json_decode(file_get_contents('/opt/apps/projects/minikids/runtime-private/config.json'),true) as $k=>$v)define($k,$v);\n$table_prefix='wp_';\nif(!defined('ABSPATH'))define('ABSPATH',__DIR__.'/');\nrequire_once ABSPATH.'wp-settings.php';\n")
PY
mysql minikeds_dev2_prod < "$STAGE/database.sql"
find "$RELEASE" -type d -exec chmod 755 {} +
find "$RELEASE" -type f -exec chmod 644 {} +
chown -R minikids:minikids "$RELEASE/wp-content"
chown root:minikids "$RELEASE/wp-config.php"
chmod 640 "$RELEASE/wp-config.php"
cd "$RELEASE"
wp --allow-root core is-installed
wp --allow-root search-replace 'http://localhost:8082/wordpress' 'https://minikids.vitorstream.shop' --all-tables-with-prefix --precise --report-changed-only
wp --allow-root search-replace 'http://localhost:8088/wordpress' 'https://minikids.vitorstream.shop' --all-tables-with-prefix --precise --report-changed-only
wp --allow-root search-replace '/wordpress/' '/' --all-tables-with-prefix --precise --report-changed-only
wp --allow-root option update home 'https://minikids.vitorstream.shop'
wp --allow-root option update siteurl 'https://minikids.vitorstream.shop'
wp --allow-root eval-file "$STAGE/production-configure.php"
wp --allow-root rewrite flush
find wp-content/mu-plugins -name '*.php' -exec php -l {} \;
echo 'Release preparada, dominio anterior sigue activo'
