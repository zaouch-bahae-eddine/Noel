function ajouterCadeauEvent(idForm) {
    $("#" + idForm).on("submit", function (event) {
        event.preventDefault();
        var data = {};
        $(this).serializeArray().forEach(obj=>{
            let name = getNameInput(obj.name);
            let value = obj.value || '';
            if (data[name]) {
                if (!Array.isArray(data[name])) {
                    data[name] = [data[name]];
                }
                data[name].push(value);
            } else {
                data[name] = value;
            }
        });
        $.ajax({
            url: '/cadeaux/ajouter',
            type: 'POST',
            data: JSON.stringify(data),

            success: function(response){
                $("#" + idForm)[0].reset();
                if(response[0]["success"]>0){
                    idCategorie = 'c-cadeau-'+data['categorie'];
                    idModification = 'm-cadeau-' + response[0]["success"];
                    idSuppression = 's-cadeau-' + response[0]["success"];
                    idPersonne = 'p-cadeau-' + response[0]["success"];
                    var e = $('<tr><td class="nom">' +
                        '</td><td class="age"></td>' +
                        '<td class="prix"></td>' +
                        '<td class="categorie" id="'+idCategorie+'"></td>' +
                        '<td class="personne"><a href="#" id="'+idPersonne+'">Personnes</a></td>' +
                        ' <td class="modifier"><a href="#" id="' + idModification + '">Modifier</a></td>' +
                        '<td class="supprimer"><a href="#" id="' + idSuppression + '">Supprimer</a></td>' +
                        '</tr>'
                    );

                    $('.nom', e).html(data['nom']);
                    $('.age', e).html(data['age']);
                    $('.prix', e).html(data['prix']);
                    $('.categorie', e).html($("#adressFormAjouter #cadeaux_categorie option[value ="+data['categorie']+"]").text());

                    $("#content").append(e);
                    $('#' + idModification).click(function (event) {
                        event.preventDefault();
                        modifierCadeau(event);
                    });
                    $('#' + idSuppression).click(function (event) {
                        event.preventDefault();
                        let idToDelete = Number(event.target.id.substring(9));
                        supprimer(idToDelete);
                    });
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                console.log("[Problem]: Ajouter cadeaux");
            }
        });
    });
}
function modifierCadeau(event, idForm) {
        idToModif = Number(event.target.id.substring(9));
        let tr = $('#' + event.target.id).closest('tr');
        let idCategorie = $('.categorie', tr).attr('id').substring(9);
        $("#"+idForm+" #cadeaux_nom").val($('.nom', tr).text());
        $("#"+idForm+" #cadeaux_age").val($('.age', tr).text());
        $("#"+idForm+" #cadeaux_prix").val($('.prix', tr).text());
        $("#"+idForm+" #cadeaux_categorie option[value=\""+idCategorie+"\"]").attr('selected', true);
}
function modifierCadeauEvent(idForm) {
    $("#" + idForm).on("submit", function (event) {
        event.preventDefault();
        var data = {};
        //[ {name: nom-de-input, value: valeur-input}, {...}, {...}, {...}... ]
        $(this).serializeArray().forEach(obj=>{
            let name = getNameInput(obj.name);
            let value = obj.value || '';
            if (data[name]) {
                if (!Array.isArray(data[name])) {
                    data[name] = [data[name]];
                }
                data[name].push(value);
            } else {
                data[name] = value;
            }
        });
        console.log(JSON.stringify(data));
        $.ajax({
            url: "/cadeaux/modifier/"+idToModif,
            type: 'POST',
            dataType: 'json',
            async: true,
            data: JSON.stringify(data),

            success: function (response) {
                let id = response[0]['success'];
                if(id == idToModif){
                    tr = $('#m-cadeau-'+id).closest("tr");
                    $('.nom',tr).html(data['nom']);
                    $('.age',tr).html(data['age']);
                    $('.prix',tr).html(data['prix']);
                    $('.categorie',tr).html($("#cadeauxFormModifier #cadeaux_categorie option[value=\""+data['categorie']+"\"]").text());
                }
            },
            error: function (xhr, status, errorThrown) {
                console.log("[probleme]: modifier cadeaux");
            }
        });
    });
}
function supprimer(idToDelete){
    $.ajax({
        url: '/cadeaux/supprimer/'+idToDelete,
        type: 'DELETE',
        async: true,

        success: function (response) {
            //Effacer la ligne dans le tableau html
            supprimerCadeauFromTable(response[0]["success"]);// response[0]["success"] est l'id du cadeau supprimmé
        },
        error: function (response) {
            console.log(response.responseJSON[0]["fail"]);
        }
    });
}
function supprimerCadeauFromTable(id){
    $('#s-cadeau-' +id).closest('tr').remove();
}