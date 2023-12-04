<?php
require_once("custom/php/common.php");

if (!connection())
{
    echo '<p>Falha na conexão com a base de dados -> ' . mysqli_connect_error() . '</p>';
}
else if (!is_user_logged_in() || !current_user_can('manage_allowed_values'))
{
    echo 'Não tem autorização para aceder a esta página';
    return;
}
else
{
    $current_page = currentPage();

    if (isset($_REQUEST['estado']) == "")
    {
        echo '
                <br>
                <table>
                <tbody style="background-color: gainsboro">
                <tr style="background-color: darkgrey">
                <th>item</th>
                <td>id</td>
                <th>subitem</th>
                <td>id</td>
                <td>valores permitidos</td>
                <td>estado</td>
                <td>ação</td>
                </tr>';

        $query_item = 'SELECT DISTINCT id, name
                       FROM item';
        $result_item = mysqli_query(connection(), $query_item);
        $num_rows_item = mysqli_num_rows($result_item);

        if ($num_rows_item <= 0)
        {
            echo '<i>Não há items.</i>';
            mysqli_free_result($result_item);
        }
        else
        {
            while ($row_item = $result_item->fetch_array())
            {
                $query_total = 'SELECT subitem.id, subitem.name 
                                FROM subitem 
                                WHERE subitem.item_id = ' . $row_item["id"] . ' 
                                        AND subitem.value_type = "enum";';
                $result_total = mysqli_query(connection(), $query_total);
                $result_total_2 = mysqli_query(connection(), $query_total);
                $num_rows_total = mysqli_num_rows($result_total);

                echo '<tr>';

                $num_rows_final = 0;

                while ($row = $result_total->fetch_array())
                {
                    $query_values = 'SELECT subitem_allowed_value.id, subitem_allowed_value.value, subitem_allowed_value.state 
                                     FROM subitem_allowed_value, subitem
                                     WHERE (subitem_allowed_value.subitem_id = subitem.id 
                                            AND subitem_id = ' . $row["id"] . ')
                                     ORDER BY subitem_allowed_value.value ASC';
                    $result_values = mysqli_query(connection(), $query_values);
                    $num_rows_values = mysqli_num_rows($result_values);

                    $num_rows_final += $num_rows_values;
                }

                $query_not_values = 'SELECT subitem.id 
                                     FROM subitem 
                                     WHERE subitem.item_id = ' . $row_item["id"] . ' 
                                            AND subitem.value_type = "enum" 
                                     EXCEPT(SELECT subitem_id 
                                            FROM subitem_allowed_value, subitem 
                                            WHERE subitem.item_id = ' . $row_item["id"] . ' 
                                                    AND subitem.value_type = "enum");';
                $result_notvalues = mysqli_query(connection(), $query_not_values);
                $num_rows_notvalues = mysqli_num_rows($result_notvalues);

                $num_rows_final += $num_rows_notvalues;

                $num_rows_finali = $num_rows_final;


                if ($num_rows_total == 0)
                {
                    echo '
                          <td colspan = 1 rowspan = 1>' . $row_item["name"] . '</td>
                          <td colspan = 6 rowspan = 1>Não há subitems especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) item(s) e depois voltar a esta opção.</td>';
                }
                else
                {
                    echo '
                          <td colspan = 1 rowspan = ' . $num_rows_finali . '>' . $row_item["name"] . '</td>';

                    while ($row_subitem = $result_total_2->fetch_array())
                    {
                        $query_values = 'SELECT subitem_allowed_value.id, subitem_allowed_value.value, subitem_allowed_value.state 
                                         FROM subitem_allowed_value, subitem
                                         WHERE (subitem_allowed_value.subitem_id = subitem.id 
                                                    AND subitem_id = ' . $row_subitem["id"] . ') ';
                        $result_values = mysqli_query(connection(), $query_values);
                        $num_rows_values = mysqli_num_rows($result_values);

                        if ($num_rows_values <= 0)
                        {
                            echo '
                                  <td>' . $row_subitem["id"] . '</td>
                                  <td>' . '[' . '
                                        <a href="' . $current_page . '?estado=introducao&subitem=' . $row_subitem["id"] . '">' . $row_subitem["name"] . '</a>' . ']' . '
                                        </td>
                                  <td colspan = 4 rowspan = 1>Não há valores permitidos definidos</td>
                                  </tr>';
                        }
                        else
                        {
                            echo '
                                    <td  colspan = 1 rowspan = ' . $num_rows_values . '>' . $row_subitem["id"] . '</td>
                                    <td  colspan = 1 rowspan = ' . $num_rows_values . '>' . '[' . '
                                            <a href="' . $current_page . '?estado=introducao&subitem=' . $row_subitem["id"] . '">' . $row_subitem["name"] . '</a>' . ' ]' . '
                                                </td>';

                            while ($row_values = $result_values->fetch_array())
                            {
                                echo '
                                        <td>' . $row_values["id"] . '</td>
                                        <td>' . $row_values["value"] . '</td>
                                        <td>' . $row_values["state"] . '</td>';

                                if ($row_values["state"] == "active")
                                {
                                    echo '
                                            <td>
                                                [editar]
                                            <br>
                                                [desativar]
                                            <br>
                                                [apagar]
                                            </td>';
                                }
                                else
                                {
                                    echo '
                                            <td>
                                                [editar]
                                            <br>
                                                [ativar]
                                            <br>
                                                [apagar]
                                            </td>';
                                }

                                echo '</tr>';
                            }
                        }
                    }
                }
            }
        }
        echo '
                </tbody>
                </table>';
    }
    else if ($_REQUEST['estado'] == "introducao")
    {

        $_SESSION["subitem_id"] = $_REQUEST["subitem"];

        echo '
                <br>
                <h3>Gestão de valores permitidos - introdução</h3>
                <form method = "request" action = "">
                <p>Valor:</p>
                <input placeholder = "Escreva aqui" name = "valor" type = "text"/>
                <input value = "confirmar" name = "estado" type = "hidden"/>
                <input value = "'.$_REQUEST["subitem"].'" name = "subitem" type = "hidden"/>
                <br>
                <br>
                <input value = "Inserir valor permitido" name  = "submit" type = "submit"/>
                </form>
                <br>';
        goBack();
    }
    else if ($_REQUEST['estado'] == "confirmar")
    {
        $valor = $_REQUEST['valor'];
        $subitem_id = $_REQUEST["subitem"];

        if(!empty($valor))
        {
            $query_subitem  ='SELECT name 
                              FROM subitem 
                              WHERE subitem.id = '.$subitem_id.' ';
            $result_subitem = mysqli_query(connection(), $query_subitem);
            $num_rows_subitem = mysqli_num_rows($result_subitem);

            while($row_sub = $result_subitem->fetch_array())
            {
                echo '
                <form method = "request" action = " ">
                <br>
                <h2>Verifique se o valor permitido inserido está correto.</h2>
                <br>
                <h3>Está a inserir o valor permitido</h3>
                <p>' . $valor . '</p>
                <h3>No subitem</h3>
                <p>' . $subitem_id . ' -' . '> ' . $row_sub['name'] . '</p>
                <input type = "hidden" name = "valor" value = " ' . $valor . ' "/>
                <input type = "hidden" name = "subitem_id" value = " ' . $subitem_id . ' "/>
                <input type = "hidden" name = "estado" value = "inserir"/>
                <br>
                <input type = "submit" name  = "submit" value = "Continuar"/>
                <br>
                <br>';
                goBack();
                echo '</form>';
            }
        }

        else
        {
            echo '
                    <br>
                    <p>Não foi inserido qualquer dado para o valor permitido.</p>
                    <p>Clique em
                                <a href = "?estado=introducao&subitem='.$subitem_id.'">voltar</a>
                                                        para tentar de novo.</p>
                    <a href ="'.$current_page.'">
                            <button>Sair para a página inicial</button>
                    </a>';
        }

    }
    else if ($_REQUEST['estado'] == "inserir")
    {
        echo '
                <br>
                <h3>Gestão de valores permitidos - inserção</h3>';

        $nom = $_REQUEST['valor'];
        $id = $_SESSION["subitem_id"];

        $insere = "INSERT INTO subitem_allowed_value (subitem_id, value, state) 
                                                 VALUES ('$id', '$nom', 'active')";

        $inserido = mysqli_query(connection(), $insere);

        if ($inserido)
        {
            echo '
                  <br>
                  <h5 style="color: lightslategrey"> Inseriu os dados de novo valor permitido com sucesso.</h5>
                  <p> Clique em 
                                <a href = " ' . $current_page . ' ">continuar</a> 
                                                                                  para voltar à página inicial. </p>';
        }
    }
};
?>