<?php
require_once("custom/php/common.php");

$query_unity = "SELECT subitem_unit_type.id, subitem_unit_type.name as tipo_nome 
                FROM subitem_unit_type 
                ORDER BY subitem_unit_type.name;";
$result_unity = mysqli_query(connection(), $query_unity);
$num_rows_unity = mysqli_num_rows($result_unity);

if(!connection())
{
    echo '<p>Falha na conexão com a base de dados -> '.mysqli_connect_error().'</p>';
}
else if (!is_user_logged_in() && !current_user_can("manage_unit_types"))
{
    echo 'Não tem autorização para aceder a esta página.';
    return;
}
else
{

    $current_page = currentPage();

    if (isset($_REQUEST['estado']) == "")
    {

        echo '
              <br>
              <table >
              <tbody style="background-color: gainsboro">
              <tr style="background-color: darkgrey">
              <th>id</th>
              <th>unidade</th>
              <th>subitem</th>
              </tr>';

        if ($num_rows_unity <= 0)
        {
            echo '<i>Não há tipos de unidades.</i>';
            mysqli_free_result($result_unity);
        }
        else
        {
            while ($row_unity = $result_unity->fetch_array())
            {
                echo '
                      <tr>
                      <td>' . $row_unity['id'] . '</td>
                      <td>' . $row_unity['tipo_nome'] . '</td>';

                $query_subitem = 'SELECT name, item_id 
                              FROM subitem 
                              WHERE subitem.unit_type_id = ' . $row_unity["id"] . ' ';
                $result_subitem = mysqli_query(connection(), $query_subitem);
                $num_rows_subitem = mysqli_num_rows($result_subitem);

                echo '<td>';
                global $ns;
                $ns = 0;

                while ($row_subitem = $result_subitem->fetch_array())
                {
                    $query_item = 'SELECT name 
                               FROM item 
                               WHERE item.id = ' . $row_subitem["item_id"] . ' ';
                    $result_item = mysqli_query(connection(), $query_item);
                    $num_rows_item = mysqli_num_rows($result_item);

                    if ($num_rows_item <= 0)
                    {
                        echo '<td>Não há itens</td>';
                        mysqli_free_result($result_item);
                    }
                    else
                    {

                        while ($row_item = $result_item->fetch_array())
                        {
                            echo $row_subitem["name"] . " (" . $row_item["name"] . ")";
                            if ($ns == $num_rows_subitem - 1)
                            {
                                echo '</td>';
                            }
                            else
                            {
                                echo ", ";
                                $ns = $ns + 1;
                            }
                        }
                    }
                }
                echo '</tr>';
            }
        }
        echo '
                </tbody>
                </table>';

        echo '
                <br>
                <h3>Gestão de unidades - introdução</h3>

                <form method = "request" action = " ">

                Nome: <input placeholder = "Escreva aqui" name = "tipo_nome" type = "text"/>
                    <input value = "confirmar" name = "estado" type = "hidden"/>
                    <br>
                    <br>
                    <input value = "Inserir tipo de unidade" type = "submit"/>
                    </form>';
    }
    else if($_REQUEST['estado'] == "confirmar"){

        if(!empty($_REQUEST['tipo_nome']))
        {
            echo '<form method = "request" action = " ">
                  <br>
                  <h3>Verifique se o novo tipo de unidade que quer inserir está correto.</h3>
                  <br>
                  <h3>Nome do novo tipo de unidade:</h3>
                  <p>' . $_REQUEST['tipo_nome'] . '</p>
                  <br>

                  <input type="hidden" name="tipo_nome" value=" ' . $_REQUEST["tipo_nome"] . ' ">
                  <input type = "hidden" name = "estado" value = "inserir"/>
                  <input type = "submit" name = "submit" value = "Continuar"/>
                  <br>
                  <br>';
            goBack();
            echo '</form>';
        }
        else
        {
            echo '
                    <br>
                    <p>Não foi inserido qualquer tipo de dado. </p>
                    <p>Clique em 
                        <a href = "'.$current_page.'">voltar</a>
                                                                para tentar de novo.</p>';
        }
    }

    else if ($_REQUEST['estado'] == "inserir")
    {
        echo '
                <h3>Gestão de unidades - inserção</h3>';

        $nom = $_REQUEST['tipo_nome'];

            $insert = "INSERT INTO subitem_unit_type (name) 
                        VALUES ('$nom')";

            $inserido = mysqli_query(connection(), $insert);

            if ($inserido) {
                echo '
                      <br>
                      <h5 style="color: lightslategrey"> Inseriu os dados de novo tipo de unidade com sucesso.</h5>
                      <p> 
                         <a href = " ' . $current_page . '">Continuar</a> 
                            </p>';
            }
    }
}
?>