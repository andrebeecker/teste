<?php

    use \Symfony\Component\HttpFoundation\Response;
    use \Symfony\Component\HttpFoundation\Request;

    date_default_timezone_set('America/Sao_Paulo');

    require_once __DIR__ . '/../vendor/autoload.php';

    $app = new Silex\Application();

    /** Connect to database*/
    $values = 'mysql:dbname=rest_api;host=127.0.0.1;charset=utf8';

    try
    {

        $connection = new PDO($values, 'root', 'root');

    }
    catch (PDOException $e)
    {

        echo 'Connection failed: ' . $e->getMessage();

    }

    /** Get all companies*/
    $app->get('/company', function () use ($app, $connection)
    {

        $query = $connection->prepare('SELECT * FROM company');
        $query->execute();
        $aCompany = $query->fetchAll(PDO::FETCH_ASSOC);

        return $app->json($aCompany);

    });

    /** Get a company by id*/
    $app->get('/company/{id}', function ($id) use ($app, $connection)
    {

        $query = $connection->prepare('SELECT * FROM company WHERE id='.$id);

        $query->execute([ $id ]);

        $aCompany = $query->fetchAll(PDO::FETCH_ASSOC);

        if(empty($aCompany))
        {

            return new Response("Empresa com id {$id} não encontrado para consulta!", 404);

        }

        return $app->json($aCompany);

    })->assert('id', '\d+');

    /** Insert a company*/
    $app->post('/company', function(Request $request) use ($app, $connection)
    {

        /** Decode Json to array*/
        $aGet = json_decode($request->getContent(), true);

        $sql = "INSERT INTO company set ";
        $sql.= " name=:name,";
        $sql.= " cnpj=:cnpj,";
        $sql.= " email=:email,";
        $sql.= " fone=:fone,";
        $sql.= " address=:address,";
        $sql.= " country=:country,";
        $sql.= " city=:city,";
        $sql.= " state=:state,";
        $sql.= " log_insert=:date";

        $query = $connection->prepare($sql);

        /** Set values from json to the sql*/
        $query->bindParam(':name', $aGet['name']);
        $query->bindParam(':cnpj', $aGet['cnpj']);
        $query->bindParam(':email', $aGet['email']);
        $query->bindParam(':fone', $aGet['fone']);
        $query->bindParam(':address', $aGet['address']);
        $query->bindParam(':country', $aGet['country']);
        $query->bindParam(':city', $aGet['city']);
        $query->bindParam(':state', $aGet['state']);
        $query->bindParam(':date', date('Y-m-d H:i:s'));

        $query->execute();

        $response = new Response('Ok', 201);
        return $response;
    });

    /** Edit a company by id*/
    $app->put('/company/{id}', function(Request $request, $id) use ($app, $connection)
    {

        /** Decode Json to array*/
        $aGet = json_decode($request->getContent(), true);

        $sql = "UPDATE company set ";
        $sql.= " name=:name,";
        $sql.= " cnpj=:cnpj,";
        $sql.= " email=:email,";
        $sql.= " fone=:fone,";
        $sql.= " address=:address,";
        $sql.= " country=:country,";
        $sql.= " city=:city,";
        $sql.= " state=:state,";
        $sql.= " log_change=:date";
        $sql.= " where id = :id";

        $query = $connection->prepare($sql);

        $query->bindParam(':id', $id);

        /** Set values from json to the sql*/
        $query->bindParam(':name', $aGet['name']);
        $query->bindParam(':cnpj', $aGet['cnpj']);
        $query->bindParam(':email', $aGet['email']);
        $query->bindParam(':fone', $aGet['fone']);
        $query->bindParam(':address', $aGet['address']);
        $query->bindParam(':country', $aGet['country']);
        $query->bindParam(':city', $aGet['city']);
        $query->bindParam(':state', $aGet['state']);
        $query->bindParam(':date', date('Y-m-d H:i:s'));

        $query->execute();

        return $app->json($aGet, 200);

    })->assert('id', '\d+');

    /** Delete a company by id*/
    $app->delete('/company/{id}', function($id) use ($app, $connection)
    {

        $query = $connection->prepare('DELETE FROM company WHERE id = :id');

        $query->bindParam(':id', $id);

        $query->execute();

        /** If not exist in database, give a error*/
        if($query->rowCount() < 1)
        {

            return new Response("Empresa com id {$id} não encontrado para exclusão!", 404);
        }

        return new Response(null, 204);

    })->assert('id', '\d+');

    $app->run();

