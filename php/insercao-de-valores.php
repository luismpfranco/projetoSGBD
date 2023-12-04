<?php
require_once("custom/php/common.php");

function formSearchChild(){
    $current_page = currentPage();

    echo '<h3>Inserção de valores - criança - procurar</h3>';
    echo 'Introduza um dos nomes da criança a encontrar e/ou a data de nascimento dela';
    echo '<br>';
    echo "<form method='get' action='$current_page'>";
        echo 'Nome <input type="text" name="child_name">';
        echo 'Data de nascimento <input type="text" name="child_birthday"><br>';
        echo '<input type="hidden" name="estado" value="escolher_crianca">';
        echo '<input type="submit">';
    echo "</form>";
}

function pickChild(){
    //Variaveis
    $child_name = $_REQUEST['child_name'];
    $child_birthday = $_REQUEST['child_birthday'];
    $query_name_child = 'SELECT name as child_name, birth_date, id, tutor_name
                         FROM child 
                         WHERE name LIKE "%'.$child_name.'%" AND
	                           birth_date LIKE "%'.$child_birthday.'%"';
    $result_name = mysqli_query(connection(),$query_name_child);

    echo '<h3> Inserção de valores - criança - escolher </h3>';
    while ($row_name = $result_name->fetch_assoc()){
        echo '<ul>';
            echo '<li> <a href="'.currentPage().'?estado=escolher_item&crianca='.$row_name["id"].'">['.$row_name["child_name"].' '.$row_name["tutor_name"].'] ('.$row_name["birth_date"].')</a></li>';
        echo '</ul>';
    }
    goBack();
}

function pickItem(){
    $_SESSION["child_id"] = $_REQUEST["crianca"];
    $query_items_type = 'SELECT DISTINCT(item_type.name) as item_type_name, item_type.id as item_type_id
                                FROM item_type, item, value, subitem
                                WHERE item.item_type_id = item_type.id AND
                         	          value.subitem_id = subitem.id AND subitem.item_id = item.id AND value.child_id = '. $_SESSION["child_id"];
    $result_items_type = mysqli_query(connection(), $query_items_type);

    echo '<h3> Inserção de valores - escolher item </h3>';
    while ($row_item_type = $result_items_type->fetch_assoc()){
        echo '<ul>';
            echo '<li> '.$row_item_type['item_type_name'].' ';

            $query_item_name = 'SELECT DISTINCT(item.name) as item_name, item.id as item_id
                                FROM item_type, item, value, subitem
                                WHERE item.item_type_id = '.$row_item_type['item_type_id'].' AND
                                      value.subitem_id = subitem.id AND subitem.item_id = item.id AND value.child_id = '. $_SESSION["child_id"];
            $result_item_name = mysqli_query(connection(), $query_item_name);
            while($row_item_name = $result_item_name ->fetch_assoc()){
                echo '<ul><li><a href="'.currentPage().'?estado=introducao&item='.$row_item_name["item_id"].'" >[ ' . $row_item_name["item_name"] . ' ]</a></li></ul>';
            }

            echo '</li>';
        echo '</ul>';
    }
    goBack();
}

function introduction(){
    $_SESSION["item_id"] = $_REQUEST["item"];
    $query_item = 'SELECT name as item_name, item_type_id FROM item WHERE item.id = '.$_SESSION["item_id"].' ';
    $result_item = (mysqli_query(connection(), $query_item)) -> fetch_assoc();
    $_SESSION["item_name"] = $result_item["item_name"];
    $_SESSION["item_type_id"] = $result_item["item_type_id"];
    $current_page = currentPage();

    $query_child_subitem = 'SELECT id as subitem_id, name as subitem_name_DB, form_field_name, form_field_type, value_type
                            FROM subitem
                            WHERE subitem.item_id = '.$_SESSION["item_id"].' AND subitem.state = "active"
                            ORDER BY subitem.form_field_order ASC';
    $result_child_subitem = mysqli_query(connection(), $query_child_subitem);

    echo '<h3> Inserção de valores - '.$_SESSION["item_name"].' </h3>';
    echo "<form name='item_type_".$_SESSION["item_type_id"]."_item_".$_SESSION["item_id"]."' method='post' action='$current_page?estado=validar&item=".$_SESSION["item_id"]."'>";
        while($row_subitem = $result_child_subitem -> fetch_assoc()){

            $subitem_name = $row_subitem["subitem_name_DB"];
            $subitem_form_name = $row_subitem["form_field_name"];
            $subitem_value_type = $row_subitem["value_type"];
            //(conforme o tipo de campo especificado na BD
            $subitem_form_type = $row_subitem["form_field_type"];

            echo '<ul>';
            echo ' <li><strong>'.$subitem_name.' ('.$subitem_value_type.') </strong></li><br>';
            switch ($subitem_value_type){
                case "text":
                    echo "<input type='$subitem_form_type' name='$subitem_form_name'><br>";
                    break;
                case "boolean":
                    echo "<input type='radio' name='$subitem_form_name' value='1'> Sim <br>";
                    echo "<input type='radio' name='$subitem_form_name' value='0'> Não <br>";
                    break;
                case "int":
                    echo "<input type='text' name='$subitem_form_name'><br>";
                    echo '';
                    break;
                case "double":
                    echo "<input type='text' name='$subitem_form_name'><br>";
                    break;
                case "enum":
                    $query_allow_value = 'SELECT subitem_allowed_value.value FROM subitem_allowed_value WHERE subitem_allowed_value.subitem_id = '.$row_subitem["subitem_id"].' ';
                    $result_allow_value = mysqli_query(connection(), $query_allow_value);
                    $num_rows = mysqli_num_rows($result_allow_value);
                    if($num_rows == 0){
                        echo '<em> Este item não tem valores permitidos </em><br>';
                    }
                    else{
                        if ($subitem_form_type == 'selectbox') {
                            echo "<select name='$subitem_form_name'>";
                            while ($row_allow_value = $result_allow_value -> fetch_assoc()){
                                echo"<option value='".$row_allow_value["value"]."'>".$row_allow_value["value"]."</option>";
                            }
                            echo "</select>";
                        }
                        else{
                            while ($row_allow_value = $result_allow_value -> fetch_assoc()){
                                echo "<input type='$subitem_form_type' name='$subitem_form_name' value='".$row_allow_value["value"]."'> ".$row_allow_value["value"]." <br>";
                            }
                        }
                    }
                    break;
                default:
                    echo 'Ocorreu um erro no processo';
            }
            echo '</ul>';
        }
    echo '<input type="hidden" name="estado" value="validar">';
    echo '<input type="submit" value="Submeter">';
    echo '</form>';
}

function validation(){

    $query_subitem_check = 'SELECT id as subitem_id, name as subitem_name, mandatory, form_field_name, value_type FROM subitem WHERE subitem.item_id = '.$_SESSION["item_id"].' ';
    $result_subitem_check = mysqli_query(connection(), $query_subitem_check);

    echo '<h3>Inserção de valores - '.$_SESSION["item_name"].' - validar</h3>';
    $boolean = False;
    $array = array();
    while($row_check = $result_subitem_check ->fetch_assoc()){
        //$field = $_REQUEST[$row_check["form_field_name"]]; //lo que metio
        if($row_check["mandatory"] == 1 && (!isset($_REQUEST[$row_check["form_field_name"]]) || trim($_REQUEST[$row_check["form_field_name"]]) == '')){
            if ($row_check["value_type"] == "enum"){
                $query_value_check = 'SELECT COUNT(value) as test FROM `subitem_allowed_value` WHERE subitem_id ='.$row_check["subitem_id"];
                $result_value_check = mysqli_query(connection(), $query_value_check);
                $test = $result_value_check ->fetch_assoc();
                if ($test["test"] == 0){
                    continue;
                }
            }
            echo "É obrigatório o preenchimento do campo <strong><em>" . $row_check["subitem_name"] . "</em></strong> <br>";
            $boolean = True;
            continue; // Salta para o proximo ciclo da proxima iteracao, nao mete os campos os vazios
        }
        $array[] = ["name" => $row_check["subitem_name"], "form_field_name" => $row_check["form_field_name"], "value" => $_REQUEST[$row_check["form_field_name"]]];
    }
    if ($boolean){
        goBack();
    }
    else{
        $current_page = currentPage();
        echo 'Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?';
        echo '<ul>';
            echo "<form method='post' action='insercao-de-valores?estado=inserir&item='$_SESSION[item_id]' '>";
            foreach ($array as $value){
                echo '<li>'.$value["name"].': '.$value["value"].'</li>';
                echo "<input type='hidden' name='".$value["form_field_name"]."' value='".$value["value"]."'>";
            }
            echo '<input type="hidden" name="estado" value="inserir"><br>';
            echo '<input type="submit" value="Sumeter">'.'<br>';
            echo "</form>";
        echo '</ul>';
    }
}

function insertData(){
    $query_insert_data = 'SELECT id as subitem_id, mandatory, form_field_name FROM subitem WHERE subitem.item_id = '.$_SESSION["item_id"].' ';
    $result_insert_data = mysqli_query(connection(), $query_insert_data);

    echo '<h3>Inserção de valores  - '.$_SESSION["item_name"].' - inserir</h3>';
    $control = False;
    while ($row_data =  $result_insert_data ->fetch_assoc()){
        $time = date("H:i:s");
        $dates = date("Y-m-d");
        //$search_data = $_REQUEST[$row_data["form_field_name"]]; //lo que metio

        if(isset($_REQUEST[$row_data["form_field_name"]])){
            $insert_db = 'INSERT INTO `value` (`child_id`, `subitem_id`, `value`, `date`, `time`)
                          VALUES ('.$_SESSION["child_id"].', '.$row_data["subitem_id"].', "'.$_REQUEST[$row_data["form_field_name"]].'", "'.$dates.'", "'.$time.'")';
            if(mysqli_query(connection(),$insert_db)){
                $control = True;
            }
            else{
                $control = False;
            }
        }
    }
    if($control){ //Para que no se me repita o texto no while
        echo 'Inseriu o(s) valor(es) com sucesso! <br>';
        echo 'Clique em Voltar para voltar ao início da inserção de valores <br>';
        echo '<a href = "wordpress/insercao-de-valores ">Voltar</a>';
    }
    else{
        echo 'Error na inserção dos dados'.'<br>';
        goBack();
    }
}

function main (){
    if(is_user_logged_in() == true && current_user_can("insert_values")){
        if(empty($_REQUEST['estado'])){
            formSearchChild();
        }
        elseif($_REQUEST['estado'] == "escolher_crianca"){
            pickChild();
        }
        elseif($_REQUEST['estado'] == "escolher_item"){
            pickItem();
        }
        elseif ($_REQUEST['estado'] == "introducao"){
            introduction();
        }
        elseif ($_REQUEST['estado'] == "validar"){
            validation();
        }
        elseif ($_REQUEST['estado'] == "inserir"){
            insertData();
        }
        return;
    };
    echo "Não tem autorização para aceder a esta página";
};
main();
