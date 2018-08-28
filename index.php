
<?php
########################################################################
#Evitando cache de arquivo  
header("Content-Type: text/html; charset=UTF-8",true); 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); 
header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

########################################################################
#INCLUDES IMPORTANTES
$pag = $_SERVER[SCRIPT_FILENAME];
$x1 = explode("\\", $pag);
$raiz = $x1[0]."\\".$x1[1]."\\".$x1[2]."\\infofixa\\infofixa.php";
$_classes = $x1[0]."\\".$x1[1]."\\".$x1[2]."\\infofixa\\Classes.class.php";
require_once ($raiz);
require_once ($_classes);

########################################################################
#Informações do Empregado
$empregadoObj = new Empregado;
$empregadoObj->informaVisitantes($conexPdo);  # REGISTRANDO ACESSO
$_MATRICULA = $empregadoObj->getMatricula();
$apresentacaoStr = $empregadoObj->getEmpregadoInfo($conexPdo);
$apresantacaoArr = array();
$apresantacaoArr = $empregadoObj->getEmpregadoArr();
$_NOME = $apresantacaoArr[1]; //NOME EMPREGADO
$_LTADM = $apresantacaoArr[2]; // LOT
$_LOFI = $apresantacaoArr[3]; // LOFI
$_LOFINOME = $apresantacaoArr[4]; //NOME DA LOFI
$_FC = $apresantacaoArr[5]; //FUNÇÃO

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Projetando</title>
    
    <!--<link href="../lib/fontawesome-free-5.0.13/web-fonts-with-css/css/fontawesome-all.css" rel="stylesheet">-->
    <!--<script defer src="../lib/fontawesome-free-5.0.13/svg-with-js/js/fontawesome-all.js"></script>-->
     <!-- css -->
     <link rel="stylesheet" href="<?php echo $caminhoCssFontAwesome;?>" TYPE="text/css" />
  
    
    <!-- css próprio -->
    <link rel="stylesheet" href="<?php echo $css_top;?>" TYPE="text/css" />
    <link rel="stylesheet" href="./css/css.css" TYPE="text/css" />

</head>
<body>

    <div id="app" class="conteudo-principal">


        <!-- Formulários Flutuantes -->
        <div class="novo-grupo" v-if="formularioNovoGrupo">
            <div class="formulario-flutuante-titulo">Novo Grupo <i class="fa fa-times fa-fw fechar-tabela" @click="formularioNovoGrupo = !formularioNovoGrupo"></i></div>
            <div class="formulario-flutuante-corpo">
                
            </div>
            
        </div>
        <div class="mask" v-if="formularioNovoGrupo"></div>
        <div class="novo-grupo" v-if="formularioEditarGrupo">
            <div class="formulario-flutuante-titulo">Editar Grupos <i class="fa fa-times fa-fw fechar-tabela" @click="formularioEditarGrupo = !formularioEditarGrupo"></i></div>
            <div class="formulario-flutuante-corpo"></div>
        </div>
        <div class="mask" v-if="formularioEditarGrupo"></div>




        <!-- Primaeira Parte >>> Topo com T´titulo e menus -->
        <div class="topo">

            <div class="titulo">

                <!-- Texto do Título -->
                <div class="titulo-texto">{{titulo}}</div>
                
                <!-- Menus Disponíveis -->
                <div class="top-menu">
                    
                    <span class="top-menu-item-text"><i class="fa fa-caret-right fa-fw"></i>Meus Projetos</span>
                    
                    <span class="top-menu-item-text"><i class="fa fa-caret-right fa-fw"></i>Projetos Compartilhados</span>

                    <span class="top-menu-item-text" v-if="areaDeTrabalho.dominiosCadastrados.length > 0" @click="mostraMenuDiv('consultandoDominios')">
                        <i class="fa fa-caret-right fa-fw"></i>
                        Meus Grupos
                        <div class="top-menu-div" v-if="menuTopDiv.consultandoDominios">
                            <div class="top-menudiv-seta"></div>
                            <div class="top-menudiv-inner menu-dominios">

                                <div class="menu-dominios-lista">
                                    <div class="dominios-lista-item" v-for="(item, key) in areaDeTrabalho.dominiosCadastrados" >
                                        <span v-if="empregadoLogado.matricula == item.nome">MEU GRUPO</span>
                                        <span v-else>{{item.nome}}</span>
                                    </div>
                                </div>
                                <div class="menu-dominios-opcoes">
                                    <div class="dominios-opcoes-item" @click="formularioNovoGrupo = true" ><i class="fa fa-plus-circle fa-fw fa-lg" ></i>Novo Grupo</div>
                                    <div class="dominios-opcoes-item" @click="formularioEditarGrupo = true" ><i class="fa fa-edit fa-fw fa-lg" ></i>Editar Grupos</div>
                                </div>

                            </div>
                        </div>
                    </span> 
                        
                    <span class="top-menu-item"><i class="fa fa-search fa-fw fa-lg"></i></span>
                    
                    <span class="top-menu-item"><i :class="{'fa fa-bookmark fa-fw fa-lg' : apenasMarcados===false, 'fa fa-bookmark-o fa-fw fa-lg' : apenasMarcados }" @click=" apenasMarcados=!apenasMarcados " ></i></span>
                    
                    <span class="top-menu-item" @click="mostraMenuDiv('consultandoInfo')">
                        <i class="fa fa-id-badge fa-fw fa-lg"></i>
                        <div class="top-menu-div" v-if="menuTopDiv.consultandoInfo">
                            <div class="top-menudiv-seta"></div>
                            <div class="top-menudiv-inner menu-info">
                                <span class="menu-info-span">{{ empregadoLogado.apresentacaoArr[1] }}</span>
                                <span class="menu-info-span">{{ empregadoLogado.apresentacaoArr[2] }}</span>
                                <span class="menu-info-span">{{ empregadoLogado.unidade }}</span>
                                

                            </div>
                        </div>
                    </span>

                    <span class="top-menu-item" @click="mostraMenuDiv('consultandoAjuda')">
                        <i class="fa fa-question-circle-o fa-fw fa-lg"></i>
                        <div class="top-menu-div" v-if="menuTopDiv.consultandoAjuda">
                            <div class="top-menudiv-seta"></div>
                            <div class="top-menudiv-inner menu-ajuda"></div>
                        </div>
                    </span>
                    
                </div>
                
            </div>
        </div>

        <!-- Segunda Parte >>> Categorias e Lista de Projetos -->
        <div class="conteudo">

            <!-- Lista de Categorias Disponíveis -->
            <!--
            <div class="categorias">
                <div class="categorias-titulo">Categorias Disponíveis</div>
                <div :class="{'categoria-ativa': categoria.selecionado}" class="categoria" v-for="categoria in categorias" @click="mudarCategoria(categoria.id, categoria.texto)">
                    <span :class="categoria.icone" class="categoria-icone"></span>
                    <span class="categoria-texto">{{categoria.texto}}</span>
                </div>
                
            </div>
            -->

            <!-- Quadro de Projetos -->
            <div class="kanban-container">

                <div class="kanban-grid">

                    <!-- Títulos de cada fase do Quadro -->
                    <div class="kanban-grid-titulos">
                        <template v-for="(blc, key) in blocos" >
                                <div  v-if="blc.aberto" :style="{ width: ((99.5-(blocosFechados*5))/(blocos.length - blocosFechados)) +'%'}" class="kanban-grid-titulos-item" :class=" {'border-r': key != (blocos.length -1) } ">
                                     <i class="fa fa-check-circle" v-if="blc.id===3"></i>
                                     {{tituloBloco[blc.id]}}
                                     <i class="fa fa-plus-circle fa-fw novo-item" v-if="blc.id===0"></i>
                                     <i v-if="blocosFechados < 3" class="fa fa-caret-left fechar-bloco" @click="fecharBloco(blc.id)"></i>
                                </div>
                                <div v-else :style="{ width: '5%'}" class="kanban-grid-titulos-item" :class=" {'border-r': key != (blocos.length -1) } ">
                                    <i class="fa fa-caret-right abrir-bloco" @click="abrirBloco(blc.id)"></i>
                                </div> 
                        </template>
                    </div>

                    <div class="kanban-grid-blocos">

                        <template v-for="(blc, key)  in blocos">

                                <div v-if="blc.aberto" :style="{ width: ((99.5-(blocosFechados*5))/(blocos.length - blocosFechados)) +'%'}" class="kanban-grid-blocos-item" :class=" {'border-r': key != (blocos.length -1) } " >
                                    
                                    <div :id="'bloco_'+blc.id" :class="{'bloco-lista-ativo': arrastandoBloco && (blc.id === (arrastandoBlocoOrigem-1) ||  blc.id === (arrastandoBlocoOrigem+1)) }" class="bloco-lista" @drop="drop" @dragover="allowDrop">



                                        <div draggable="true" @dragstart="drag" :origem="'bloco_'+item.fase" :fase="'fase_'+item.fase" :id="'item_'+item.id" :style="{'background-color': item.color}" class="bloco-lista-item" v-for="(item, key2) in listaProjetos" v-if="item.fase === blc.id && item.categoria === categoriaSelecionada">

                                            <div class="item-arrastar" v-if="arrastandoBloco && arrastandoBlocoId !== 'item_'+item.id">Arraste até aqui para colocar uma posição ACIMA</div>
                                            <div class="item-titulo"><i class="fa fa-bookmark-o fa-fw fa-lg"></i>{{item.titulo}}</div>
                                            <div class="item-prazo">D{{item.prazo}}</div>
                                            <template v-if="item.aberto">
                                                <div class="item-descricao">{{item.descricao}}</div>
                                                <div class="item-dono"><b>Criado por:</b> {{item.dono}} em {{item.data}}</div>
                                                <div class="item-desenvolvedores" v-if="item.participantes.length > 0"><b>Participante(s):</b> <span v-for="desenvolvedor in item.participantes">{{desenvolvedor}}</span></div>
                                            </template>
                                            <div class="item-comandos">

                                                <template v-if="item.dono===empregadoLogado.matricula || item.participantes.indexOf(empregadoLogado.matricula) != -1">
                                                    
                                                    <span  class="fa fa-download fa-fw fa-lg comandos-icone"></span>
                                                    <span  class="fa fa-share-alt fa-fw fa-lg comandos-icone"></span> 
                                                    <span  v-if="areaDeTrabalho.dominiosCadastrados.length > 1" class="fa fa-paper-plane-o fa-fw fa-lg comandos-icone"></span> 

                                                </template>   

                                                <span class="fa fa-paperclip fa-fw fa-lg comandos-icone"></span>

                                                <span class="fa fa-external-link-square fa-fw fa-lg  comandos-icone"></span>

                                                <template>
                                                    <span v-if="item.aberto" class="fa fa-angle-double-up fa-fw fa-lg  comandos-icone" @click="redimensionarItem(item.id)"></span>
                                                    <span v-else class="fa fa-angle-double-down fa-fw fa-lg  comandos-icone"  @click="redimensionarItem(item.id)"></span>
                                                </template>
                                             
                                            </div>

                                        </div>



                                        
                                        

                                    </div>

                                </div>
                                <div v-else :style="{ width: '5%'}" class="kanban-grid-blocos-item" :class=" {'border-r': key != (blocos.length -1) } ">

                                </div>
                           
                        </template>
                    </div>

                </div>

            </div>
           
        </div>

    </div>
    
    
    <script src="<?php echo $jquery;?>"></script>
    <script src="<?php echo $vue;?>"></script>
    <script src="<?php echo $axios;?>"></script>
    <script src="<?php echo $highcharts;?>"></script>
    <script src="./js/app.js"></script>

</body>
</html>
