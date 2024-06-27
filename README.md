# Simplified Banking

## Steps to run the project locally
- Setup .env: ```cv .env.example .env```
- Setup docker: ```docker compose up -d```
- Access app container: ```docker compose exec app bash```. At the container:
    - run migrations + seeds: ```php artisan migrate:fresh --seed```
    - generate a jwt secret: ```php artisan jwt:secret```
    - run tests: ```php artisan test```
    - start notify queue: ```php artisan queue:work --queue=notify```
- Test code quality: ```docker run -it --rm -v $(pwd):/project -w /project jakzal/phpqa phpmd app text cleancode,codesize,controversial,design,naming,unusedcode```