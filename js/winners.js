$(document).ready(function () {
    $("#contentTable").DataTable({
        scrollY: 500,
        processing: true,
        serverSide: true,
        ajax: {
            url: "php/get-all-winners.php",
            type: "POST",
            dataSrc: function (json) {
                console.log(json);
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
            { data: "id_game" },
            { data: "id_card" },
            { data: "premio_ganho" },
            { data: "valor_premio", render: $.fn.dataTable.render.number('.', ',', 2, 'R$') },
            { data: "data" },
        ],
        pagingType: "full_numbers",
        responsive: true,
        columnDefs: [
            { orderable: false, targets: "no-sort" }
        ],
        order: [[8, 'desc']],
    });
});