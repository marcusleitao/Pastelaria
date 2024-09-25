# Pastelaria

Siga os passos abaixo para rodar a aplicação na sua máquina

> **Requisitos:** É necessário que tenha instalado em sua máquina o Docker e o Docker Compose

## 1º) Clone o repositório em sua máquina
**Após clonar, acesse a pasta do repositório atraves da sua IDE, copie o conteúdo do arquivo .env.example e cole em um novo arquivo que você vai nomear como .env**

## 2º) Acesse a pasta do repositório pelo terminal e rode o seguinte comando
```
docker-compose up -d
```

## 3º) Instale o Composer, rode as Migrations e os Seeders

Após subir todos os containers da aplicação, rode os seguintes comandos **(um de cada vez e na ordem especificada abaixo)**

```
docker-compose exec app composer install
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

## 4º) Importe a Collection para seu Insomnia ou Postman
Baixe a collection pelo [clicando aqui](https://drive.google.com/file/d/1SgwNVsckngZivNCqLv74vl5sFEk_KwG5/view?usp=sharing) e depois importe no seu Insomnia ou Postman
> OBS: Esta collection foi criada a partir do Insomnia

## Para Executar os testes
utilize o comando
```
docker-compose run --rm test
```