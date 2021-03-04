# Filas e Processamento Assíncrono com Laravel

- Algumas vezes nossas aplicações recebem uma demanda muito grande de solicitações e muitas vezes essas solicitações são pesadas, ou seja, tem um tempo de processamento demorado e o tempo de resposta também é demorado, como o envio de um e-mail. Se a aplicação precisa processar algo pesado com um tempo de resposta grande o ideal é liberar o usuário, evitando o travamento da aplicação e executando essa tarefa enquanto o servidor estiver ocioso. Além de melhorar a usabilidade isso ajuda a trabalhar de forma mais inteligente, sem sobrecarregar com múltiplos processos simultâneos. Mas como podemos fazer tudo isso? Este projeto te mostrará como através do uso de filas.


## Passo 1: Criando e configurando o projeto

- O primeiro passo é criarmos um projeto Laravel, podemos fazer isso com o seguinte comando:

```
composer create-project laravel/laravel --prefer-dist laravel-queues
```

- Após criado o projeto precisamos alterar o nosso arquivo ```.env```. Dentro do arquivo existe a linha ```QUEUE_CONNECTION = sync```, precisamos trocar o ```sync``` por ```database```, isso porque utilizando o sync ele fará com que os jobs sejam executados simultâneamente e não em fila. Para verificar os drivers disponíveis basta acesssar o arquivo ```config/queue.php```, nele são listadas as possíveis conexões.

- Nós ainda não temos tabela para salvar os trabalhos executados pelo nosso job, então iremos rodar o seguinte comando:

```
php artisan queue:table
```

- Este comando irá criar duas migrations: jobs e failed_jobs. Os jobs executados com sucesso serão armazenados em jobs e os que possuírem erros serão armazenados em failed_jobs. Agora basta executar estas migrations com o comando ```php artisan migrate:fresh``` que as tabelas serão criadas.


## Passo 2: Criando e configurando o Job

- O laravel possuí um conceito chamado Job, que é algo que será executado quando for solicitado, e é ele que utilizaremos para processar e executar nossa fila.



