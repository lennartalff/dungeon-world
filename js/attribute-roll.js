
jQuery.ajaxSetup({ cache: false });
setTimeout(dw_request_rolls, 1000);

function dw_attribute_roll_clicked(name) {
    var rolls = dw_roll(6, count = 2);
    var roll_sum = rolls.reduce((a, b) => a + b, 0);
    var mod = parseInt(document.getElementById(`${name}-mod-select`).value);
    var total = roll_sum + mod;
    var roll_text = `[${rolls.join(', ')}] + (${mod}) = ${total}`;
    var character_name = document.getElementById("character_name").value;
    if (character_name === "") {
        character_name = 'Zu doof, einen Namen einzugeben';
    }
    jQuery.ajax({
        type: "post",
        url: ajax.url,
        data: {
            action: "dw_add_roll_to_db",
            nonce: ajax.nonce,
            "roll_text": roll_text,
            "roll_comment": dw_get_comment(total, name),
            "roll_type": name,
            "roll_user": character_name
        },
        success: function () {
            // location.reload();
            clearTimeout(dw_request_rolls);
            dw_request_rolls();
        }
    });
}

function dw_get_comment(result, attr) {
    if (result <= 6) {
        return dw_get_fail_comment(attr);
    }
    if (result <= 9) {
        return dw_get_mixed_comment(attr);
    }
    return dw_get_success_comment(attr);
}

function dw_get_fail_comment(attr) {
    if (attr === "STR") {
        var available_strings = [
            "Du bist ein richtiges Tier! Allerdings eher ne Maus... Vielleicht solltest du die XP sinnvoll reinvestieren",
            "Stark sein ist nicht alles. Immerhin gibt es XP. Nützt nur nichts, wenn du stirbst!",
            "Die Idee war gut. Leider waren die Arme zu schwach... Immerhin wiegen XP nichts."

        ];
    } else if (attr === "DEX") {
        var available_strings = [
            "Über die eigenen Füße gefallen... Wenigstens laufen die XP nicht davon.",
            "Ups! Ausgerutscht. Aber dabei XP gefunden.",
            "Wert sich so ungeschickt anstellt, sollte lieber keine gefährlichen Sachen machen. Oder die neuen XP in Geschicklichkeit investieren."
        ];
    } else if (attr === "CON") {
        var available_strings = [
            "Hoffentlich bist du nicht der Tank! XP gibts trotzdem.",
            "Alles in allem machst du keinen besonders robusten Eindruck. Hier gibt es XP. Aber nicht alles auf einmal ausgeben!",
            "Tot bringen dir die XP auch nichts mehr..."
        ];
    } else if (attr === "INT") {
        var available_strings = [
            "Smart ist anders. Zu oft auf den Kopf gefallen? Investier doch mal ein paar XP.",
            "Das Gehirn nutzt sich beim Denken nicht ab. Kannst du ruhig mal ausprobieren."
        ];
    } else if (attr === "WIS") {
        var available_strings = [
            "Wissen ist Macht! Besonders mächtig war das aber nicht... Mit dem Erfahrungspunkt kannst du das ja ändern.",
            "Hoffentlich hast du nicht gerade versucht, die Mächte der Elementargeister zu rufen. Das wäre nämlich schiefgegangen."
        ];
    } else if (attr === "CHA") {
        var available_strings = [
            "Du bist nicht so attraktiv wie du denkst!",
            "Es gibt bestimmt eine Situation, in der du mit deiner Ausstrahlung glänzen kannst. Diese ist es nicht."
        ];
    } else {
        var available_strings = [
            "Auf was hast du denn gewürfelt? Auf jeden Fall ist es ordentlich schiefgegangen."
        ]
    }

    var i = dw_generate_rand(0, available_strings.length - 1);
    return available_strings[i];
}

function dw_get_mixed_comment(attr) {
    var available_strings = ["Das geht noch besser. Aber auch schlechter.",
        "Es ist noch kein Meister vom Himmel gefallen.",
    ];
    var i = dw_generate_rand(0, available_strings.length - 1);
    return available_strings[i];
}

function dw_get_success_comment(attr) {
    var available_strings = ["Für dich auf jeden Fall eine beeindruckende Leistung!",
        "So macht man das!",
        "Da gibts echt nichts zu meckern!"];
    var i = dw_generate_rand(0, available_strings.length - 1);
    return available_strings[i];
}

function dw_roll(size, count = 1) {
    var rolls = [];
    for (var i = 0; i < count; i++) {
        rolls.push(dw_generate_rand(1, size));
    }
    return rolls;
}

function dw_generate_rand(min, max) {
    return Math.floor(Math.random() * (max - min)) + min;
}

function dw_request_rolls() {
    var id = parseInt(document.getElementById('dw_last_roll_id').value);
    jQuery.ajax({
        type: "post",
        url: ajax.url,
        dataType: "json",
        data: {
            action: "dw_get_rolls",
            nonce: ajax.nonce,
            "dw_last_roll_id": id
        },
        success: function (data) {
            // alert(`${id}/${data.dw_last_roll_id}`);
            if (data.success) {
                dw_refresh_rolls(data.rolls, data.dw_last_roll_id);
            }
        },
        complete: function () {
            setTimeout(dw_request_rolls, 2000);
        }
    });
}

function dw_refresh_rolls(rolls, last_id) {
    var i;
    table = document.getElementById('roll_table');
    for (i = 0; i < rolls.length; i++) {
        dw_create_roll_element(table, rolls[i]);
    }
    var id_el = document.getElementById('dw_last_roll_id');
    id_el.value = last_id;
}

function dw_create_roll_element(table, roll) {
    var row = document.createElement('tr');
    var name = `<td>${roll.name}</td>`;
    var type = `<td>${roll.type}</td>`;
    var roll_result = `<td>${roll.roll}</td>`;
    var comment = `<td>${roll.comment}</td>`;
    row = '<tr>' + name + type + roll_result + comment + '</tr>';
    table.insertRow(1).innerHTML = row;
}