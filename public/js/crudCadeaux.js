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
                        '<td class="personne"><a href="#" ><img width="24px" src="../img/icon/personne.png" id="'+idPersonne+'"/></a></td>' +
                        ' <td class="modifier"><a href="#" ><img width="24px" src="../img/icon/edit.png" id="' + idModification + '" /></a></td>' +
                        '<td class="supprimer"><a href="#" ><img width="24px" src="../img/icon/delete.png" id="' + idSuppression + '" /></a></td>' +
                        '</tr>'
                    );

                    $('.nom', e).html(data['nom']);
                    $('.age', e).html(data['age']);
                    $('.prix', e).html(data['prix']);
                    $('.categorie', e).html($("#cadeauxFormAjout #cadeaux_categorie option[value ="+data['categorie']+"]").text());

                    $("#content").append(e);
                    $('#' + idModification).click(function (event) {
                        event.preventDefault();
                        modifierCadeau(event);
                        $('#modifModal').modal('show');
                    });
                    $('#' + idSuppression).click(function (event) {
                        event.preventDefault();
                        let idToDelete = Number(event.target.id.substring(9));
                        supprimer(idToDelete);
                    });
                }
            },
            error: function (response) {
                console.log(response);
                Swal.fire({
                    icon: 'error',
                    title: 'Action échoué',
                    text: response.responseJSON["fail"]
                });
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Modification effectué'
                    });
                }
            },
            error: function (response) {
                console.log("[probleme]: modifier cadeaux");
                Swal.fire({
                    icon: 'error',
                    title: 'Action échoué',
                    text: response.responseJSON["fail"]
                });
            }
        });
    });
}
function supprimer(idToDelete){
    $.confirm({
        title: 'Supprimer !',
        content: 'Voullez vous vraiment supprimer cet utilisateur?',
        type: 'red',
        typeAnimated: true,
        buttons: {
            tryAgain: {
                text: 'Supprimer',
                btnClass: 'btn-red',
                action: function(){
                    $.ajax({
                        url: '/cadeaux/supprimer/'+idToDelete,
                        type: 'DELETE',
                        async: true,

                        success: function (response) {
                            //Effacer la ligne dans le tableau html
                            supprimerCadeauFromTable(response[0]["success"]);// response[0]["success"] est l'id du cadeau supprimmé
                        },
                        error: function (response) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Action échoué',
                                text: response.responseJSON[0]["fail"]
                            });
                        }
                    });
                }
            },
            close: {
                text: 'Annuler',
                action: function () {
                }
            }
        }
    });


}
function supprimerCadeauFromTable(id){
    $('#s-cadeau-' +id).closest('tr').remove();
}