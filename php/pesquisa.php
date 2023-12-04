<?php
require_once("custom/php/common.php");

function itemSearch(){
    $query_items_type = 'SELECT DISTINCT(item_type.name) as item_type_name, item_type.id as item_type_id
                                FROM item_type, item, value, subitem
                                WHERE item.item_type_id = item_type.id AND
                         	          value.subitem_id = subitem.id AND subitem.item_id = item.id AND value.child_id = '. $_SESSION["child_id"];
    $result_items_type = mysqli_query(connection(), $query_items_type);

    echo '<h3> Pesquisa - escolher item </h3>';
    while ($row_item_type = $result_items_type->fetch_assoc()){
        echo '<ul>';
            echo '<li> '.$row_item_type['item_type_name'].' ';
                $query_item_name = 'SELECT DISTINCT(item.name) as item_name, item.id as item_id
                                        FROM item_type, item, value, subitem
                                        WHERE item.item_type_id = '.$row_item_type['item_type_id'].' AND
                                              value.subitem_id = subitem.id AND subitem.item_id = item.id AND value.child_id = '. $_SESSION["child_id"];
                $result_item_name = mysqli_query(connection(), $query_item_name);
                while($row_item_name = $result_item_name ->fetch_assoc()){
                    echo '<ul><li><a href="'.currentPage().'?estado=escolha&item='.$row_item_name["item_id"].'" >[ ' . $row_item_name["item_name"] . ' ]</a></li></ul>';
                }
            echo '</li>';
        echo '</ul>';
    }
}

function userChoice(){
    $_SESSION["item_id"] = $_REQUEST["item"];
    $query_search_item_name = 'SELECT name as item_nameDB FROM item WHERE id = '. $_SESSION["item_id"];
    $result_search_item_name = mysqli_query(connection(), $query_search_item_name) ->fetch_assoc();
    $_SESSION["item_name"] = $result_search_item_name["item_nameDB"];

    $querty_show_attributes = 'SHOW COLUMNS FROM child';
    $result_show_attributes = mysqli_query(connection(), $querty_show_attributes);

    $current_page = currentPage();
    echo "<form method='post' action='$current_page'>";
    //TABELA N1: lista dos atributos da tabela child (OPTION 1)
    echo "<p><em>Escolha as opcoes desejadas da criança</em></p>";
    while ($row_show_attributes = $result_show_attributes->fetch_array()){

            echo '<table>';
                echo '<tbody>';
                    if($row_show_attributes["Field"] === "name"){
                        echo'<td>Nome da criança</td>';
                    }
                    elseif($row_show_attributes["Field"] === "birth_date"){
                        echo'<td>Data de nascimento</td>';
                    }
                    elseif($row_show_attributes["Field"] === "tutor_name"){
                        echo'<td>Nome do tutor</td>';
                    }
                    elseif($row_show_attributes["Field"] === "tutor_phone"){
                        echo'<td>Telemovel do tutor</td>';
                    }
                    elseif($row_show_attributes["Field"] === "tutor_email"){
                        echo'<td>Email do tutor</td>';
                    }
                    if($row_show_attributes["Field"] !== "id"){
                        echo '<td><input type="checkbox" name='.$row_show_attributes["Field"].'_field'.' value="1"></td>';
                        echo '<td ><input type="checkbox" name='.$row_show_attributes["Field"].' value="0"></td>';
                    }
                echo '</tbody>';
            echo '</table>';
    }

    $query_listof_subitem = 'SELECT form_field_name as form_name, name as subitem_name FROM subitem WHERE subitem.item_id='.$_SESSION["item_id"];
    $result_listof_subitem = mysqli_query(connection(), $query_listof_subitem);
    //TABELA N2: lista dos subitens do item escolhido
    echo '<p><em>Escola as carateristicas desejadas da criança</em></p>';

    while ($row_listof_subitem = $result_listof_subitem->fetch_assoc()){
        echo '<table>';
            echo '<tbody>';
                echo '<td>'.$row_listof_subitem["subitem_name"].'</td>';
                echo '<td><input type="checkbox" name='.$row_listof_subitem["form_name"].' value="1"></td>';
                echo '<td ><input type="checkbox" name='.$row_listof_subitem["form_name"].' value="0"></td>';
            echo '</tbody>';
        echo '</table>';
    }
    echo "<br>";
    echo '<input type="hidden" name="estado" value="escolher_filtros">';
    echo '<input type="submit" value="Submeter">';
    echo "</form>";
}

function pickFilter(){
    $show_attributes = 'SHOW COLUMNS FROM child';
    $result_attributes = mysqli_query(connection(), $show_attributes);
    $_SESSION['attributes'] = array();

    while ($row_attributes = $result_attributes->fetch_array()){
        if (isset($_REQUEST[$row_attributes["Field"].'_field'])){
            if($_REQUEST[$row_attributes["Field"].'_field'] == 1){
                $_SESSION['attributes'][] = array("Field" => $row_attributes["Field"], "Type" => $row_attributes["Field"]);
            }
        }

    }

    $_SESSION['subitens'] = array();
    $query_listof_subitem = 'SELECT value_type, form_field_name as form_name, name as subitem_name, id as subitem_id FROM subitem WHERE subitem.item_id='.$_SESSION["item_id"];
    $result_listof_subitem = mysqli_query(connection(), $query_listof_subitem);
    while ($row_listof_subitem = $result_listof_subitem->fetch_assoc()){
        if (isset($_REQUEST[$row_listof_subitem["form_name"]])){
            if($_REQUEST[$row_listof_subitem["form_name"]] == 1){
                $_SESSION['subitens'][] = array("name" => $row_listof_subitem["subitem_name"], "id" => $row_listof_subitem["subitem_id"]);
            }
        }
    }

    echo 'Irá ser realizada uma pesquisa que irá obter, como resultado, uma listagem de, para cada criança, dos seguintes dados pessoais escolhidos: <br>';
    echo '<ul>';
    foreach ($_SESSION['attributes'] as $att){
        echo '<li>';
        echo $att["Field"];
        echo '</li>';
    }
    echo '</ul>';

    echo 'e do item: nome_do_item (em variável de sessão) uma listagem dos valores dos subitens: <br>';
    echo '<ul>';
    foreach ($_SESSION['subitens'] as $subitem){
        echo '<li>';
        echo $subitem["name"];
        echo '</li>';
    }
    echo '</ul>';

    goBack();
}

function selectBox($input){
    $operators = ['>', '>=', '=', '<', '<=', '!=',  'LIKE'];

}

function main (){
    if(is_user_logged_in() == true && current_user_can("search")){
        if(empty($_REQUEST['estado'])){
            itemSearch();
        }
        elseif($_REQUEST['estado'] == "escolha"){
            userChoice();
        }
        elseif ($_REQUEST['estado'] == "escolher_filtros"){
            pickFilter();
        }
        return;
    };
    echo "Não tem autorização para aceder a esta página";
};

main();
