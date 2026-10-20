set -euo pipefail
umask 077
BASE=/opt/apps/projects/minikids
RELEASE=$BASE/releases/toycommerce-20261020
test -f "$RELEASE/wp-config.php"
test -f "$BASE/private/access-dev2.txt"
test ! -e "$BASE/previous-20261020"
# Snapshot final antes del cambio. El sitio y DB anteriores se conservan.
/usr/local/sbin/minikids-backup full
cp /etc/nginx/sites-available/minikids.conf "$BASE/private/nginx-before-dev2.conf"
cp /usr/local/sbin/minikids-backup "$BASE/private/backup-before-dev2.sh"
python3 - <<'PY'
from pathlib import Path
p=Path('/etc/nginx/sites-available/minikids.conf');s=p.read_text()
s=s.replace('location = /wp-config.php', 'location ~* \\.bak(?:-|$) { deny all; }\n    location ^~ /wp-content/qa-evidencia/ { deny all; }\n    location ^~ /local-private/ { deny all; }\n    location ^~ /database/ { deny all; }\n    location = /wp-config.php')
p.write_text(s)
p=Path('/usr/local/sbin/minikids-backup');s=p.read_text().replace(' minikids_dev |', ' minikeds_dev2_prod |').replace('tar -I \'gzip -1\'', 'tar -h -I \'gzip -1\'')
s=s.replace('"$BASE/private/database.json"', '"$BASE/private/database-dev2.json" "$BASE/runtime-private" "$BASE/mail-private"')
p.write_text(s)
PY
nginx -t
mv "$BASE/current" "$BASE/previous-20261020"
ln -s "$RELEASE" "$BASE/current"
rollback() {
  unlink "$BASE/current"
  mv "$BASE/previous-20261020" "$BASE/current"
  cp "$BASE/private/nginx-before-dev2.conf" /etc/nginx/sites-available/minikids.conf
  cp "$BASE/private/backup-before-dev2.sh" /usr/local/sbin/minikids-backup
  systemctl reload nginx
}
if ! systemctl reload nginx; then rollback; exit 1; fi
systemctl reload php8.3-fpm
if ! curl -ksSf --resolve minikids.vitorstream.shop:443:127.0.0.1 https://minikids.vitorstream.shop/ -o "$BASE/private/home-dev2.html"; then rollback;exit 1;fi
if ! grep -q minikids-estetica "$BASE/private/home-dev2.html"; then rollback;exit 1;fi
/usr/local/sbin/minikids-backup full
echo 'ToyCommerce activo; respaldo anterior y DB conservados'
