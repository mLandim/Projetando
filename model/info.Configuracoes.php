<?php 
########################################################################
#INCLUDES IMPORTANTES
$pag = $_SERVER[SCRIPT_FILENAME];
$x1 = explode("\\", $pag);
$raiz = $x1[0]."\\".$x1[1]."\\".$x1[2]."\\infofixa\\infofixa.php";
$_classes = $x1[0]."\\".$x1[1]."\\".$x1[2]."\\infofixa\\Classes.class.php";
require_once ($raiz);
require_once ($_classes);

#######################################################################
# COLETANDO INFORMAÇÕES - USANDO O ARQUIVO Classes.class.php

$empregadoObj = new Empregado; # INSTANCIANDO NOVO OBJETO COM BASE NA CLASSE Empregado
$apresantacaoArr = array();
$apresentacaoStr = $empregadoObj->getEmpregadoInfo($conexPdo);
$apresantacaoArr = $empregadoObj->getEmpregadoArr();
$matricula = $empregadoObj->matricula;
$_NOME = $apresantacaoArr[1];
$_LOT= $apresantacaoArr[2]; // LOT
$_LOFI = $apresantacaoArr[3]; // LOFI
$_LOFI_NOME = $apresantacaoArr[4]; //NOME DA LOFI
$_FC = $apresantacaoArr[5]; //FUNÇÃO
$acessoEspecial = $empregadoObj->getAcessoEspecial($matricula); // CONFERINDO ACESSO ESPECIAL
$matriculaOk = strtoupper($matricula);


#######################################################################
# MONTANDO OBJETO DE RESPOSTA

###########################
# INFORMAÇÕES GERAIS
$arrInterno["MATRICULA"] = strtoupper($matricula);
$arrInterno["USUARIO_LOGADO"] = array(strtoupper($matricula), $_NOME, $_FC );
$arrInterno["ACESSO_ESPECIAL"] = 0;//$acessoEspecial;
$arrInterno["APRESENTACAO"] = $empregadoObj->getApresentacao();
$arrInterno["UNIDADE_ADM"] = $_LOT;
$arrInterno["UNIDADE_LOFI"] = $_LOFI;



#######################################################################
# VERIFICANDO DOMINIOS (ESPAÇOS DE TRABALHO)

$_dominios = array();


$sqlAcessos = "SELECT COUNT(*) AS CT_ACESSOS FROM APP_SPAONLY_PROJETOS_ACESSOS WHERE MATRICULA = '$matriculaOk'";
$sqlAcessosC = mssql_query($sqlAcessos,$conexao);
$sqlAcessosR = mssql_fetch_array($sqlAcessosC);

if((int)$sqlAcessosR['CT_ACESSOS']===0){

    $sqlDom1 = "INSERT INTO APP_SPAONLY_PROJETOS_CONF_DOMINIOS (DOMINIO, DATA_CADASTRO, RESPONSAVEL_CADASTRO)
    VALUES ('$matriculaOk', GETDATE(), 'SISTEMA')";
    $sqlQry = mssql_query($sqlDom1, $conexao);

    $sqlConDom1 = "SELECT ID FROM APP_SPAONLY_PROJETOS_CONF_DOMINIOS WHERE DOMINIO = '$matriculaOk'";
    $sqlConDom1C = mssql_query($sqlConDom1,$conexao);
    $sqlConDom1R = mssql_fetch_array($sqlConDom1C);
    $_domId = (int)$sqlConDom1R['ID'];

    $sqlDom1 = "INSERT INTO APP_SPAONLY_PROJETOS_CONF_DOMINIOS_USUARIOS (DATA_CADASTRO, DOMINIO_ID, DOMINIO_NOME, MATRICULA, NOME, UNIDADE, PERFIL, PRINCIPAL)
    VALUES (GETDATE(), '$_domId', '$matriculaOk', '$matriculaOk', '$_NOME', '$_LOT', 1, 1)";
    $sqlQry = mssql_query($sqlDom1, $conexao);

    $_dominios[] = array('id' => $_domId, 'nome' => $matriculaOk, 'perfil' => 0, 'principal' => 1);

}else{

    $sqlDominio = "SELECT * FROM APP_SPAONLY_PROJETOS_CONF_DOMINIOS_USUARIOS WHERE MATRICULA = '$matriculaOk' ORDER BY DOMINIO_ID";
    $sqlDominioC = mssql_query($sqlDominio,$conexao);
    while($sqlDominioR = mssql_fetch_array($sqlDominioC)){
        $_dominios[] = array('id' => (int)$sqlDominioR['DOMINIO_ID'], 'nome' => $sqlDominioR['DOMINIO_NOME'], 'perfil' => (int)$sqlDominioR['PERFIL'], 'principal' => (int)$sqlDominioR['PRINCIPAL']);
    }

}    

$arrInterno["DOMINIOS"] = $_dominios;



###########################
# CATEGORIA INICIAL -> PARA ESPAÇO DE TRABALHO PESSOAL
$_categorias[] = array('id' => 0, 'ordem' => 0, 'selecionado' => true , 'acesso' => 'PRIVADO',  'texto' => utf8_encode('Meus Projetos'), 'icone' => 'fa fa-id-badge fa-2x' );
$arrInterno["CATEGORIAS"] = $_categorias;




#######################################################################
# GRAVANDO ACESSOS
$bwsObj = new ConfereBrowser;
$bws = $bwsObj->browserId();
$nav = $bws['browser'];
$ver = $bws['version'];

$pag = $_SERVER['PHP_SELF'];
$url = $_SERVER['HTTP_HOST'];
$remoteHost = $_SERVER['REMOTE_ADDR'];
$_host = gethostbyaddr($remoteHost);

$date_default = new DateTime();
$dataHoje =  $date_default->format('m/d/Y');
$dataHojeCompleta =  $date_default->format('m/d/Y H:i:s');

$sql = "INSERT INTO APP_SPAONLY_PROJETOS_ACESSOS (DATA, DATA_TIME, MATRICULA, NOME, FC, LOT_ADM, LOT_FIS,  IP, HOST, NAVEGADOR, N_VERSAO)
 VALUES ( '$dataHoje', '$dataHojeCompleta','$matriculaOk', '$_NOME', '$_FC', '$_LOT', '$_LOFI','$remoteHost', '$_host', '$nav', '$ver')";
$sqlQry = mssql_query($sql, $conexao);



$arrayLinhas[] = $arrInterno;
echo json_encode($arrInterno);
