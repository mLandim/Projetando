<?php
########################################################################
#INCLUDES IMPORTANTES
header("Content-Type: text/html; charset=UTF-8",true);
$pag = $_SERVER['SCRIPT_FILENAME'];
$x1 = explode("\\", $pag);
$raiz = $x1[0]."\\".$x1[1]."\\".$x1[2]."\\infofixa\\infofixa.php";
$_classes = $x1[0]."\\".$x1[1]."\\".$x1[2]."\\infofixa\\Classes.class.php";
include ($raiz);
include ($_classes);


	$_dominio = $_REQUEST['dominio'];
    $arrayLinhasFinal = array();
	$arrayLinhas = array();

	$sqlDemandas = "SELECT ID, CONVERT(CHAR(10), DATA_CADASTRO, 103) AS DC, DATEDIFF(day,DATA_CADASTRO, GETDATE()) AS DTDIF, DOMINIO, ACESSO, CATEGORIA, FASE, POSICAO_BLOCO, NIVEL_PRIORIDADE, COMPLEXIDADE, NOME, DESCRICAO, DONO_PROJETO, GESTOR 
                    FROM  APP_SPAONLY_PROJETOS_TAB WHERE DOMINIO='$_dominio' ORDER BY DOMINIO, CATEGORIA, FASE, POSICAO_BLOCO, ID ";
	$sqlDemandasC = mssql_query($sqlDemandas, $conexao);
	while($sqlDemandasR = mssql_fetch_array($sqlDemandasC)){

            $_id = (int)$sqlDemandasR["ID"];

            $arrInterno["id"] = (int)$sqlDemandasR["ID"];
            $arrInterno["dominio"] = utf8_encode($sqlDemandasR["DOMINIO"]);
            $arrInterno["categoria"] = (int)$sqlDemandasR["CATEGORIA"];
            $arrInterno["fase"] = (int)$sqlDemandasR["FASE"];
            $arrInterno["posicao"] = (int)$sqlDemandasR["POSICAO_BLOCO"];
            $arrInterno["color"] = '#ffffb3';
            $arrInterno["aberto"] = true;
            $arrInterno["draggable"] = true;
            $arrInterno["data"] = $sqlDemandasR["DC"];
            $arrInterno["dono"] =  $sqlDemandasR["DONO_PROJETO"];

            ###########################################
            # PARTICIPANTES DO PROJETO
            $_participantes = array();
            $sqlParticipantes = "SELECT ID, ID_PROJETO, CONVERT(CHAR(10), DATA_ATUALIZACAO, 103) AS DC, MATRICULA 
            FROM  APP_SPAONLY_PROJETOS_TAB_PARTICIPANTES WHERE ID_PROJETO='$_id' ORDER BY ID, DATA_ATUALIZACAO";
            $sqlParticipantesC = mssql_query($sqlParticipantes, $conexao);
            while($sqlParticipantesR = mssql_fetch_array($sqlParticipantesC)){
                $_participantes[] = array('id' => (int)$sqlParticipantesR['ID'], 'id_projeto' => (int)$sqlParticipantesR['ID_PROJETO'], 'data_atualizacao'=> $sqlParticipantesR['DC'], 'matricula'=> $sqlParticipantesR['MATRICULA']);
            }
            $arrInterno["participantes"] = $_participantes;

            ###########################################
            # COMPARTILHAMENTOS
            $_compartilhamentos = array();
            $sqlShare = "SELECT ID, ID_PROJETO, CONVERT(CHAR(10), DATA_ATUALIZACAO, 103) AS DC, MATRICULA 
            FROM  APP_SPAONLY_PROJETOS_TAB_COMPARTILHAMENTOS WHERE ID_PROJETO='$_id' ORDER BY ID, DATA_ATUALIZACAO";
            $sqlShareC = mssql_query($sqlShare, $conexao);
            while($sqlShareR = mssql_fetch_array($sqlShareC)){
                $_compartilhamentos[] = array('id' => (int)$sqlShareR['ID'], 'id_projeto' => (int)$sqlShareR['ID_PROJETO'], 'data_atualizacao'=> $sqlShareR['DC'], 'matricula'=> $sqlShareR['MATRICULA']);
            }
            $arrInterno["colaboradores"] = $_compartilhamentos;

            ###########################################
            # REQUISITOS
            $_requisitos = array();
            $sqlShare = "SELECT ID,  CONVERT(CHAR(10), DATA_CADASTRO, 103) AS DC, ID_PROJETO, FASE_REQUISISTO, NOME, DESCRICAO, RESPONSAVEL, NIVEL_PRIORIDADE, POSICAO_BLOCO, COMPLEXIDADE 
            FROM  APP_SPAONLY_PROJETOS_TAB_REQUISITOS WHERE ID_PROJETO = '$_id' ORDER BY  FASE_REQUISISTO, POSICAO_BLOCO, ID";
            $sqlShareC = mssql_query($sqlShare, $conexao);
            while($sqlShareR = mssql_fetch_array($sqlShareC)){
                $_requisitos["id"] = (int)$sqlShareR["ID"];
                $_requisitos["fase"] = $sqlShareR["FASE_REQUISISTO"];
                $_requisitos["posicao"] = $sqlShareR["POSICAO_BLOCO"];
                $_requisitos["color"] = '#ffad99';
                $_requisitos["titulo"] = utf8_encode($sqlShareR["NOME"]);
                $_requisitos["descricao"] =  utf8_encode($sqlShareR["DESCRICAO"]);
                $_requisitos["data"] = $sqlShareR["DC"];
                $_requisitos["dono"] =  $sqlShareR["RESPONSAVEL"];
                $_requisitos["prioridade"] = $sqlShareR["NIVEL_PRIORIDADE"];
                $_requisitos["complexidade"] = $sqlShareR["COMPLEXIDADE"];

                
            
            }
            $arrInterno["listaRequisitos"] = $_requisitos;

            ###########################################
            # EVOLUÇÃO
            $_evolucao = array();
            $sqlEvo = "SELECT ID,  CONVERT(CHAR(10), DATA_ATUALIZACAO, 103) AS DC, ID_PROJETO, RESPONSAVEL, FASE_ANTERIOR, NOVA_FASE
            FROM  APP_SPAONLY_PROJETOS_TAB_EVOLUCAO WHERE ID_PROJETO = '$_id' ORDER BY  DATA_ATUALIZACAO";
            $sqlEvoC = mssql_query($sqlEvo, $conexao);
            while($sqlEvoR = mssql_fetch_array($sqlEvoC)){

                $_evolucao["id"] = (int)$sqlEvoR["ID"];
                $_evolucao["data"] = $sqlEvoR["DC"];
                $_evolucao["responsavel"] = $sqlEvoR["RESPONSAVEL"];
                $_evolucao["fase_anterior"] = (int)$sqlEvoR["FASE_ANTERIOR"];
                $_evolucao["nova_fase"] = (int)$sqlEvoR["NOVA_FASE"];

            }
            $arrInterno["evolucao"] = $_evolucao;


            $arrInterno["titulo"] = utf8_encode($sqlDemandasR["NOME"]);
            $arrInterno["descricao"] =  utf8_encode($sqlDemandasR["DESCRICAO"]);
            $arrInterno["prioridade"] = (int)$sqlDemandasR["NIVEL_PRIORIDADE"];
            $arrInterno["acesso"] = $sqlDemandasR["ACESSO"];
            $arrInterno["complexidade"] = (int)$sqlDemandasR["COMPLEXIDADE"];
            $arrInterno["gestor"] =  $sqlDemandasR["GESTOR"];
            $arrInterno["prazo"] =  $sqlDemandasR["DTDIF"];
  

			$arrayLinhas[] = $arrInterno;
		
    }
    
    $arrayLinhasFinal["LISTA_PROJETOS"] = $arrayLinhas;
    	
    echo json_encode($arrayLinhasFinal);
    /*
    {id:0, fase:1, categoria: 0, posicao:0, color:'#ffad99', aberto: true, draggable: true,
        data:'15/05/2018', dono:'C078868', participantes:['C078868'], colaboradores:[], listaRequisitos:[],
        titulo:'Quadro Kanban', prazo:2, descricao:'Webapp para organização de Projetos'}
    */
    /* {id:0, fase:1, categoria: 0, posicao:0, color:'#ffad99', aberto: true, draggable: true,
                    data:'15/05/2018', dono:'C078868', participantes:['C078868'], colaboradores:[], listaRequisitos:[],
                    titulo:'Quadro Kanban', prazo:2, descricao:'Webapp para organização de Projetos'},

                    {id:1, fase:0, categoria:  0, posicao:0, color:'#ffffb3', aberto: true, draggable: false,
                    data:'20/05/2018', dono:'C012345', participantes:[],  colaboradores:[], listaRequisitos:[],
                    titulo:'Painel de Controle', prazo:15, descricao:'Compilação de visualizações gráficas para controle dos sistemas de responsabilidade da CICOC'},
                    
                    {id:2, fase:0, categoria:  1, posicao:1, color:'#ffffb3', aberto: true, draggable: true,
                    data:'02/04/2018', dono:'C654321', participantes:[], colaboradores:[], listaRequisitos:[],
                    titulo:'Matriz de Conformidade', prazo:12, descricao:'Relação de Subcontas para acompanhamento prioritário'},
                    
                    {id:3, fase:0, categoria: 1, posicao:1,  color:'#ffffb3', aberto: true, draggable: true,
                    data:'12/02/2018', dono:'C010101', participantes:[], colaboradores:[], listaRequisitos:[],
                    titulo:'Atualiza Painel de Incoerências', prazo:9, descricao:'Atualização e correção de vários controles'}*/