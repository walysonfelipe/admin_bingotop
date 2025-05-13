$(document).ready(function() {
    $("#contentTable").DataTable({
            scrollY: 500,
            processing: true, // Mostra um indicador de carregamento
            serverSide: true, // Ativa carregamento via AJAX
            ajax: {
                url: "php/get-all-deposits.php", // Arquivo PHP que busca os dados
                type: "POST",
                dataSrc: function (json) {
                    console.log(json); // Verifique a resposta JSON aqui
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
                { data: "transaction_date" },
                { data: "quantia", render: $.fn.dataTable.render.number('.', ',', 2, 'R$') },
                { data: "saldo_bonus", render: $.fn.dataTable.render.number('.', ',', 2, 'R$') },
                { data: "transaction_src" },
                { data: "status", orderable: false },
            ],
            order: [[4, 'desc']]
    });
});