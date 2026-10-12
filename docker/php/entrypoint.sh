#!/bin/sh
# 그누보드7 컨테이너 부팅 시퀀스 (멱등 — 재시작해도 안전)
set -e
cd /var/www/html

# 1. 쓰기 디렉토리 보장 (storage 마운트 볼륨 대응)
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 2. .env 없으면 예시에서 생성
if [ ! -f .env ]; then
  cp .env.example .env 2>/dev/null || touch .env
fi

# 3. APP_KEY 없으면 생성 후 .env 에 영속화 (암호화 데이터 호환 필수 — 매번 바꾸면 안 됨)
if ! grep -q "^APP_KEY=.\+" .env 2>/dev/null; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

# 4. DB 대기 (mysql 호스트가 뜨기 전 migrate 방지)
if [ -n "$DB_HOST" ]; then
  echo "Waiting for database ($DB_HOST)..."
  for i in $(seq 1 30); do
    if php -r '$c=@fsockopen(getenv("DB_HOST"), (int)(getenv("DB_PORT") ?: 3306)); if($c){fclose($c); exit(0);} exit(1);' 2>/dev/null; then
      break
    fi
    sleep 2
  done
fi

# 5. storage 심볼릭 링크
php artisan storage:link --force 2>/dev/null || true

# 6. 마이그레이션 (RUN_MIGRATIONS=false 면 스킵)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "Running migrations..."
  php artisan migrate --force
fi

# 7. 번들 확장 반영 (코드 업그레이드 시 활성 디렉토리 동기화)
if [ "${RUN_EXTENSION_UPDATE:-true}" = "true" ]; then
  echo "Syncing bundled extensions..."
  php artisan module:update sirsoft-ecommerce --force 2>/dev/null || true
  php artisan plugin:update sirsoft-flower_delivery --force 2>/dev/null || true
  php artisan template:update sirsoft-basic --force 2>/dev/null || true
fi

# 8. 설정 캐시 (라우트 캐시는 만들지 않음 — 클로저 라우트 안전 기본값)
php artisan config:cache 2>/dev/null || true

echo "Boot sequence done. Starting $@"
exec "$@"
