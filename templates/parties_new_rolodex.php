<?php
include("../text_editor/ed/functions.php");
include("../text_editor/ed/datacon.php");

$query = "SELECT * FROM `cse_partie_type` ORDER BY blurb";
$result = DB::runOrDie($query);
?>
<div class="gridster parties_new" id="gridster_parties_new"
     style="display:none; text-align:left; margin-top:13px; border:0px solid red; width:900px">
    <span class="form_title">Add Partie</span>
    <ul>
        <?php
        $row_counter    = 1;
        $column_counter = 1;
        while ($row = $result->fetch()) {
            $partie_type_id = $row->partie_type_id;
            $partie_type    = $row->partie_type;
            $blurb          = $row->blurb;
            $color          = $row->color;
            ?>

            <li id="partie_type_nameGrid" data-row="<?php echo $row_counter; ?>"
                data-col="<?php echo $column_counter; ?>" data-sizex="1" data-sizey="1"
                class="partie gridster_border gridster_holder"
                style="background:url(img/glass<?php echo $color; ?>.png) left top; border:#FFFFFF solid 1px ; -moz-border-radius: 3px; -webkit-border-radius: 3px; -khtml-border-radius: 3px; border-radius: 3px; padding:5px; font-family: 'Open Sans', sans-serif; color:#FFFFFF; margin-top:0px"
                onclick="document.location.href='#newpartie/<?php echo $blurb; ?>'">
                <div style="float:right"></div>
                <a style="color:white; text-decoration:none" href="#newpartie/<?php echo $blurb; ?>"
                   title="Click to edit <?php echo $partie_type; ?>"><?php echo $partie_type; ?></a>
            </li>
            <?php
        }
        $column_counter++;
        if (($column_counter % 4) == 0) { //FIXME: this is definitely wrong
            //new row
            $row_counter++;
            $column_counter = 1;
        }
        ?>
    </ul>
</div>
