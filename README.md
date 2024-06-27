# Simplified Banking

Simplified payments platform. In simplified-banking it is possible to deposit money, make transfers and withdraw, all under the mediation of external services (mock).
There are 3 types of users:
- **Admin**: does not have access to the wallet or transfers, can only view the operations carried out by users in the application;
- **Usual**: has access to the wallet and has the power to deposit, transfer and withdraw money;
- **Merchants**: have access to the wallet but can only receive transfers and withdraw.

## Steps to run the project locally
- Setup .env: ```cv .env.example .env```
- Setup docker: ```docker compose up -d```
- Access app container: ```docker compose exec app bash```. At the container:
    - run migrations + seeds: ```php artisan migrate:fresh --seed```
    - generate a jwt secret: ```php artisan jwt:secret```
    - run tests: ```php artisan test```
    - start notify queue: ```php artisan queue:work --queue=notify```
- Test code quality: ```docker run -it --rm -v $(pwd):/project -w /project jakzal/phpqa phpmd app text cleancode,codesize,controversial,design,naming,unusedcode```