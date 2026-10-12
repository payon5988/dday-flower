# 그누보드7 + 꽃배달 쇼핑몰 (dday-flower) 프로덕션 이미지
# PHP 8.3-FPM + G7 필수 확장 16종 + 운영 선택 확장
FROM php:8.3-fpm

# 시스템 패키지 (gd/intl/zip/pcntl/posix 등에 필요)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libicu-dev libonig-dev libxml2-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
    pdo_mysql mbstring exif gd intl bcmath pcntl posix zip opcache \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer (멀티스테이지 없이 바이너리만 복사)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP 운영 설정
COPY docker/php/php.ini /usr/local/etc/php/conf.d/g7.ini

WORKDIR /var/www/html

# 소스 복사 후 의존성 설치 (dist/ 커밋본 그대로 사용 — npm 빌드 불필요)
COPY . /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction \
  && php artisan package:discover --ansi || true

# Entrypoint: DB 대기 → APP_KEY → migrate → 확장 반영 → 캐시 → php-fpm
COPY docker/php/entrypoint.sh /usr/local/bin/g7-entrypoint.sh
RUN chmod +x /usr/local/bin/g7-entrypoint.sh

ENTRYPOINT ["g7-entrypoint.sh"]
CMD ["php-fpm"]
