$(document).ready(function() {
    $("#contentTable").DataTable({
        scrollY: 500,
        processing: true, // Mostra um indicador de carregamento
        serverSide: true, // Ativa carregamento via AJAX
        ajax: {
            url: "php/get-all-users.php", // Arquivo PHP que busca os dados
            type: "POST",
            dataSrc: function (json) {
                return json.data;
            }
        },
        language: {
            "decimal": ",",
            "thousands": ".",
            "sEmptyTable": "Nenhum registro encontrado",
            "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered": "(Filtrados de _MAX_ registros)",
            "sInfoPostFix": "",
            "sInfoThousands": ".",
            "sLengthMenu": "_MENU_ resultados por página",
            "sLoadingRecords": "Carregando...",
            "sProcessing": "Processando...",
            "sZeroRecords": "Nenhum registro encontrado",
            "sSearch": "Buscar:",
            "oPaginate": {
                "sNext": "Próximo",
                "sPrevious": "Anterior",
                "sFirst": "Primeiro",
                "sLast": "Último"
            },
            "oAria": {
                "sSortAscending": ": Ordenar colunas de forma ascendente",
                "sSortDescending": ": Ordenar colunas de forma descendente"
            }
        },
        columns: [
            { data: "nome" },
            { data: "cpf" },
            { data: "email" },
            { data: "telefone" },
            { data: "codigo" },
            { data: "saldo", render: $.fn.dataTable.render.number('.', ',', 2, 'R$') },
            { data: "saldo_bonus", render: $.fn.dataTable.render.number('.', ',', 2, 'R$') },
            { data: "data_cadastro" },
            { data: "userSrc" },
            { data: "depositos_realizados" },
            { data: "depositos_concluidos" },
            { data: "total_depositado", render: $.fn.dataTable.render.number('.', ',', 2, 'R$') },
            { data: "total_premios", render: $.fn.dataTable.render.number('.', ',', 2, 'R$') },
            { data: "lucro", render: $.fn.dataTable.render.number('.', ',', 2, 'R$') },
            { data: "editar", orderable: false }
        ],
        pagingType: "full_numbers", // Melhor paginação
        responsive: true,  
        columnDefs: [
            { orderable: false, targets: "no-sort" } // Desativa ordenação onde a classe "no-sort" estiver
        ]
    });
});

$(document).on("click", "#closePopup, #cancelBtn", function() {
    $("#backgroundWrapper").hide();
    $("#popupWrapper").fadeOut(200);
});

$(document).on("click", ".editBtn", function() {
    let uid = $(this).data("userid");
    $.ajax({
        url: "php/edit-user-popup.php",
        type: "POST",
        data: { uid: uid },
        success: function(data) {
            $("#popupWrapper").html(data);
            $("#backgroundWrapper").show();
            $("#popupWrapper").show();
        },
    })
});

$(document).on("click", "#saveBtnGreen", function() {
    var uid = $("#uid").val();
    var nome = $("#nome").val();
    var cpf = $("#cpf").val();
    var email = $("#email").val();
    var telefone = $("#telefone").val();
    var codigo = $("#codigo").val();
    var saldo = $("#saldo").val();
    var saldoBonus = $("#saldoBonus").val();

    $.ajax({
        url: "php/update-user.php",
        type: "POST",
        data: {
            uid: uid,
            nome: nome,
            cpf: cpf,
            email: email,
            telefone: telefone,
            codigo: codigo,
            saldo: saldo,
            saldoBonus: saldoBonus
        },
        success: function (data) {
            $("#backgroundWrapper").hide();
            $("#popupWrapper").fadeOut(200);
        }
    });
});