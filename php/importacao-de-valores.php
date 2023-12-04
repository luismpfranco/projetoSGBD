<?php
require_once("custom/php/common.php");

if(!connection())
{
    echo '<p>Falha na conexão com a base de dados -> ' . mysqli_connect_error() . '</p>';
}
else if (!is_user_logged_in() && !current_user_can('values_import'))
{
    echo 'Não tem autorização para aceder a esta página';
    return;
}
else
{
    $current_page = connection();

    echo '<br>';
    if (isset($_REQUEST['estado']) == "")
    {
        echo '
                <h3>Importação de valores - escolher criança</h3>';

        echo '
                <table style="background-color: gainsboro">
                <tbody >
                <tr style="background-color: darkgrey">
                <td>
                    <strong>Nome</strong>
                    </td>
                <td>
                    <strong>Data de nascimento</strong>
                    </td>
                <td>
                    <strong>Enc. de educação</strong>
                    </td>
                <td>
                    <strong>Telefone do Enc.</strong>
                    </td>
                <td>
                    <strong>e-mail</strong>
                    </td>
                </tr>';

        $query_child = 'SELECT * 
                        FROM child 
                        ORDER BY name ASC';
        $result_child = mysqli_query(connection(), $query_child);
        $num_rows_child = mysqli_num_rows($result_child);

        if($num_rows_child <= 0)
        {
            echo '
                    <td>Não há crianças.</td>';
        }
        else
        {
            while($row_child = $result_child->fetch_array())
            {
                echo '
                        <tr>
                        <td>' .'
                            <a href="importacao-de-valores?estado=escolheritem&crianca=' . $row_child["id"] . '">' . $row_child["name"] . '</a>' . '
                                </td>
                        <td>'.$row_child['birth_date'].'</td>
                        <td>'.$row_child['tutor_name'].'</td>
                        <td>'.$row_child['tutor_phone'].'</td>
                        <td>'.$row_child['tutor_email'].'</td>';
                /*echo '<form method = "request"  action = " ">
                                <input type = "hidden" name = "id_crianca" value = "'.$row_child["id"].'"/> 
                                </form>';*/
            }
            echo '</tr>';
        }
        echo '
                </tbody>
                </table>';
    }
    else if($_REQUEST['estado'] == "escolheritem")
    {

        $id_child = $_REQUEST["crianca"];

        $query_item_type = 'SELECT id as id_tipo, name as nome_tipo
                            FROM item_type';
        $result_item_type = mysqli_query(connection(), $query_item_type);
        $num_rows_item_type = mysqli_num_rows($result_item_type);

        if($num_rows_item_type <= 0)
        {
            echo '<i>Não tem tipos de itens especificados.</i>';
        }
        else
        {

            while($row_item_type = $result_item_type->fetch_array())
            {
                $query_item = 'SELECT DISTINCT item.id as id_item, item.name as nome_item, item.item_type_id as tipo_nome
                            FROM item, subitem
                            WHERE (item.state = "active"
                                    AND item.id = subitem.item_id
                                    AND item.item_type_id = '.$row_item_type['id_tipo'].' )';
                $result_item = mysqli_query(connection(), $query_item);
                $num_rows_item = mysqli_num_rows($result_item);

                if($num_rows_item <= 0)
                {
                    echo '<i>Este tipo de item não tem itens.</i>';
                }
                else
                {
                    if($row_item_type['nome_tipo'] == "dado_de_crianca")
                    {
                        echo '
                            <ul>
                            <li>dado de crianca</li>
                                <ul>';
                    }
                    else
                    {
                        echo '
                            <ul>
                            <li>'.$row_item_type['nome_tipo'].'</li>
                            <ul>';
                    }

                    while($row_item = $result_item->fetch_array())
                    {
                        echo'
                                <li>' . '[' . '
                                      <a href = " ?estado=introducao&crianca=' .$id_child. '&item='.$row_item['id_item'].' "> ' . $row_item['nome_item'] . '</a>' . ' ]' . '
                                      </li>';

                    }
                    echo '
                            </ul>';
                }
                echo '
                        </ul>';
            }
        }
    }
    else if($_REQUEST['estado'] == "introducao")
    {
        $item_id = $_REQUEST['item'];
        $crianca = $_REQUEST['crianca'];

        $query_form_names = 'SELECT subitem.id, subitem.form_field_name, subitem.value_type
                             FROM subitem
                             WHERE subitem.item_id = '.$item_id.';';
        $result_form_names = mysqli_query(connection(), $query_form_names);
        $result_form_names1 = mysqli_query(connection(), $query_form_names);
        $result_form_names2 = mysqli_query(connection(), $query_form_names);
        $num_rows_form_names = mysqli_num_rows($result_form_names);

        if($num_rows_form_names <= 0)
        {
            echo '<i>Não existem form_field_names.</i>';
        }
        else
        {
            echo '<table>';
            echo '<tbody>';
            echo '<tr>';
            while($row_form_names = $result_form_names->fetch_array())
            {
                $query_values = 'SELECT subitem_allowed_value.id, subitem_allowed_value.value
                                 FROM subitem_allowed_value
                                 WHERE subitem_id = '.$row_form_names['id'].';';
                $result_values = mysqli_query(connection(), $query_values);
                $num_rows_values = mysqli_num_rows($result_values);

                if($row_form_names['value_type'] == "enum")
                {
                    for ($i = 0; $i < $num_rows_values; $i++)
                    {
                        echo '<td>' . $row_form_names['form_field_name'] . ' </td>';
                    }
                }
                else
                    echo '<td>'.$row_form_names['form_field_name'].' </td>';
            }
            echo '</tr>';
            echo '<tr>';
            while($row_form_sub_ids = $result_form_names1->fetch_array())
            {
                $query_values = 'SELECT subitem_allowed_value.id, subitem_allowed_value.value
                                 FROM subitem_allowed_value
                                 WHERE subitem_id = '.$row_form_sub_ids['id'].';';
                $result_values = mysqli_query(connection(), $query_values);
                $num_rows_values = mysqli_num_rows($result_values);

                if($row_form_sub_ids['value_type'] == "enum")
                {
                    for ($i = 0; $i < $num_rows_values; $i++)
                    {
                        echo '<td align="right">' . $row_form_sub_ids['id'] . ' </td>';
                    }
                }
                else
                    echo '<td align="right">'.$row_form_sub_ids['id'].'</td>';
            }
            echo '</tr>';
            echo '<tr>';
            while($row_values = $result_form_names1->fetch_array())
            {
                $query_values1 = 'SELECT subitem_allowed_value.value
                                 FROM subitem_allowed_value
                                 WHERE subitem_id = '.$row_values['id'].';';
                $result_values1 = mysqli_query(connection(), $query_values1);
                $num_rows_values1 = mysqli_num_rows($result_values1);

                if($row_values['value_type'] == "enum")
                {
                    echo '<td align="right">' . $row_form_sub_ids['value'] . ' </td>';
                }
                else
                    echo '<td></td>';
            }
            echo '</tr>';
        }
        echo '</table>
              </tbody>';

    }
}
