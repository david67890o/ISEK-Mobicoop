php bin/console doctrine:database:create --if-not-exists -n &&
php bin/console doctrine:migrations:migrate -n &&
openssl genrsa -aes256 -passout pass:ca4ffed31ee358cc7c7083af6e5773cd -out config/jwt/private.pem 4096 &&
openssl rsa -in config/jwt/private.pem -passin pass:ca4ffed31ee358cc7c7083af6e5773cd -pubout -out config/jwt/public.pem &&
chmod 777 config/jwt/* 