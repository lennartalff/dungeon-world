<?php

class dw_GenericAttributeForm
{
    public string $attr_name;
    public string $roll_button_name;
    public string $mod_select_name;

    public function __construct(string $name)
    {
        $this->attr_name = $name;
        $this->roll_button_name = $name . "-roll-button";
        $this->mod_select_name = $name . "-mod-select";
    }

    public function get_form()
    {
        $mods = [-3, -2, -1, 0, 1, 2, 3];
        $options = "";
        foreach ($mods as $val) {
            $options .= "<option value=\"" . $val . "\">" . $val . "</option>";
        }
        $output = "<p>" . $this->attr_name . "<br>";
        $output .= $this->create_mod_select($this->mod_select_name) . "<br>";
        $output .= $this->create_roll_button($this->roll_button_name);
        $output .= "</p>";

        return $output;
    }

    function create_roll_button(string $name)
    {
        return "<button type=\"button\" id=\"" . $name . "\" onclick=\"dw_attribute_roll_clicked('" . $this->attr_name ."')\">Roll</button>";
    }

    function create_mod_select(string $name)
    {
        $mods = [-3, -2, -1, 0, 1, 2, 3];
        $options = "";
        foreach ($mods as $val) {
            $options .= "<option value=\"" . $val . "\">" . $val . "</option>";
        }
        return "<select id=\"" . $name . "\">" . $options . "</select>";
    }
}

function dw_roll_table()
{
    global $wpdb, $table_prefix;
    $dw_first_time = true;
    $dw_output = '';
    $dw_lastout = '';
    $last_id = 0;
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $table_prefix . "dw_rolls ORDER BY id DESC"));
    $output = '<div style="overflow-y: scroll; height:400px;" id="roll_table_container"><table id="roll_table" style="table-layout:fixed;width: 100%;">';
    $output .= '<colgroup><col span="1" style="width: 20%;">';
    $output .= '<col span="1" style="width: 10%;">';
    $output .= '<col span="1" style="width: 20%;">';
    $output .= '<col span="1" style="width: 50%;">';
    $output .= '</colgroup>';
    $output .= "<tr><th>Name</th><th>Type</th><th>Roll</th><th>Comment</th></tr>";
    if ($results) {
        foreach ($results as $r) {
            $name = sanitize_text_field($r->name);
            $type = sanitize_text_field($r->type);
            $roll = sanitize_text_field($r->roll);
            $comment = sanitize_text_field($r->comment);
            $id = intval(sanitize_text_field($r->id));

            if ($id > $last_id) {
                $last_id = $id;
            }
            $output .= "<tr><td>" . $name . "</td><td>" . $type . "</td><td>" . $roll . "</td><td>" . $comment . "</td></tr>";
        }
    }
    $output .= "</table>";
    $output .= '<input type="hidden", id="dw_last_roll_id" value="' . $last_id . '"/>';
    $output .= "</div>";
    return $output;
}

function dw_attribute_table()
{    
    $output = "";
    $output .= '<label for="character_name">Name: </label>';
    $output .= '<input type="text" id="character_name"/><br>';
    $attribute_first = array('STR', 'DEX', 'CON');
    $attribute_second = array('INT', 'WIS', 'CHA');
    $output .= '<div id="attribute_table_container">';
    $output .= '<table>';
    $output .= dw_create_attr_row($attribute_first);
    $output .= dw_create_attr_row($attribute_second);
    $output .= '</table>';
    $output .= '</div>';
    return $output;
}

function dw_create_attr_row(array $attributes)
{
    $output = '<tr>';
    foreach ($attributes as $attr) {
        $form = new dw_GenericAttributeForm($attr);
        $output .= '<td>' . $form->get_form() . '</td>';
    }
    $output .= '</tr>';
    return $output;
}
