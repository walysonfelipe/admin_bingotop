<?php

function renderSidebarNavigation()
{
?>
    <nav class="sidebar-navigation">
        <ul>
            <li onclick="window.location.href='resultados.php'">
                <i class="fa-solid fa-calendar-days"></i>
                <span class="tooltip">Resultado diário</span>
            </li>
            <li onclick="window.location.href='metrics.php'">
                <i class="fa-solid fa-chart-simple"></i>
                <span class="tooltip">Métricas</span>
            </li>
            <?php if (isset($_COOKIE['privileges']) && $_COOKIE['privileges'] == 2): ?>
                <li onclick="window.location.href='admin.php'">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span class="tooltip">Saques (EzzeBank)</span>
                </li>
            <?php endif; ?>
            <!-- <?php //if (isset($_COOKIE['privileges']) && $_COOKIE['privileges'] == 2): 
                    ?>
                <li onclick="window.location.href='Saque.php'">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span class="tooltip">Saques (SuitPay)</span>
                </li>
            <?php //endif; 
            ?> -->
            <li onclick="window.location.href='winners.php'">
                <i class="fa-solid fa-trophy"></i>
                <span class="tooltip">Ganhadores</span>
            </li>
            <li onclick="window.location.href='depositos.php'">
                <i class="fa-solid fa-wallet"></i>
                <span class="tooltip">Depósitos</span>
            </li>
            <li onclick="window.location.href='jogos.php'">
                <i class="fa-solid fa-dice"></i>
                <span class="tooltip">Jogos</span>
            </li>
            <li onclick="window.location.href='apostas.php'">
                <i class="fa-solid fa-coins"></i>
                <span class="tooltip">Apostas</span>
            </li>
            <li class="active" onclick="window.location.href='usuarios.php'">
                <i class="fa-solid fa-users"></i>
                <span class="tooltip">Usuários</span>
            </li>
            <?php if (isset($_COOKIE['privileges']) && $_COOKIE['privileges'] == 2): ?>
                <li onclick="window.location.href='configuracoes.php'">
                    <i class="fa-solid fa-gears"></i>
                    <span class="tooltip">Configurações</span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php
}
