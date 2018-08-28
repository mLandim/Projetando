
//OBJETO DADOS DO APP
var data = {

    //Geral > Título da Página
    titulo: 'Projetando',

    //Menu > Define se apenas os marcados serão exibidos
    apenasMarcados: false,

    //Menu > Define se o detalhe de cada menu será exibido
    menuTopDiv:{

        consultandoInfo:false,
        consultandoDominios:false,
        consultandoAjuda:false,

    },

    formularioNovoGrupo:false,
    formularioEditarGrupo:false,
    

    //Configuracões > Dados da área de trabalho
    areaDeTrabalho:{

        dominio: null, //Domínio atual
        dominiosCadastrados:[] // Domínos cadastrados para o usuário

    },

    //Configuracões > Dados do empregado logado
    empregadoLogado:{
        matricula: null,
        unidade:null,
        apresentacao:null,
        apresentacaoArr: [],
        perfil:0,
        senhaTemp:null
    },
    

    //Configuracões > Categorias cadastradas para o domínio
    categorias:[],
    //Configuracões > Desfinindo categoria que inicia selecionada
    categoriaSelecionada:0,
    
    //Configuracões > Definindo texto de apresentação para cada fase
    tituloBloco:['Em Espera', 'A Fazer', 'Em Andamento', 'Concluído'],
    //Configuracões > Definindo estrutura e estado de cada fase
    blocos:[
        {id:0, aberto:true, titulo:'Em Espera'},
        {id:1, aberto:true, titulo:'A Fazer'},
        {id:2, aberto:true, titulo:'Em Andamento'},
        {id:3, aberto:true, titulo:'Concluído'}
    ],
    //Configuracões > Contador de blocos fecados -> Importante para recalcular a largura dos blocos que permanecem abertos
    blocosFechados:0,

    //Configuracões > Se há um item sendo arrastado
    arrastandoBloco:false,
    //Configuracões > id do bloco "Pai" que receberá o item
    arrastandoBlocoId:null,
    //Configurações > index do item selecionado na lista
    indexLista:null,
    //Configuracões > id do bloco "Pai" Origem do item
    arrastandoBlocoOrigem:null,

    //DB > Lista de projetos cadastrados para o domínio
    listaProjetos:[]

}

//APP VUE
var vue = new Vue({

    el: '#app',
    data: data,
    created: function(){
        var self = this;
        //Iniciando chamadas - sistema
        this.inicializaConfiguracoes();
    },
    methods:{

        //Captura Dados do Empregado e outras Configurações
        inicializaConfiguracoes: function(){
           
            var self = this;

            //Capturando dados do empregado e depois carregando base
            $.ajaxSetup({ cache: false}); //Impedindo o cache na leitura dos arquivos json
            $.getJSON('./model/info.Configuracoes.php' , function(data){

                self.empregadoLogado.matricula = data.MATRICULA;
                self.empregadoLogado.unidade = data.UNIDADE_LOFI;
                self.empregadoLogado.apresentacao = data.APRESENTACAO;
                self.empregadoLogado.apresentacaoArr = data.USUARIO_LOGADO;
                self.categorias = data.CATEGORIAS;
                self.categoriaSelecionada = data.CATEGORIAS[0].id;

                //self.areaDeTrabalho.dominio = data.DOMINIOS[0];

                self.areaDeTrabalho.dominiosCadastrados = data.DOMINIOS;
                for (var index = 0; index < self.areaDeTrabalho.dominiosCadastrados.length; index++) {
                    var element = self.areaDeTrabalho.dominiosCadastrados[index];
                    if(element.principal===1){
                        self.areaDeTrabalho.dominio = element;
                    }
                }


                self.capturaDataBase();
               


            });
            
        },

        //Capturando Base de Projetos
        capturaDataBase: function(){
            var self = this;

            $.ajaxSetup({ cache: false}); //Impedindo o cache na leitura dos arquivos json
            $.getJSON('./model/info.listaProjetos.php?dominio='+self.areaDeTrabalho.dominio.nome , function(data){

               
                self.listaProjetos = data.LISTA_PROJETOS;


            });

        },

        //Exibindo Menu no topo da página
        mostraMenuDiv: function(propriedade){

            var self = this;
          
            //console.log('>> '+propriedade);
            var keyNames = Object.keys(self.menuTopDiv);
            //console.log(keyNames);
            for (var index = 0; index < keyNames.length; index++) {
                var element = keyNames[index];
                
                if(element==propriedade){
                    //console.log('V '+element);
                    self.menuTopDiv[element] = ! self.menuTopDiv[element];
                }else{
                    //console.log('F '+element);
                    self.menuTopDiv[element] = false;
                }
            }
            
        },

        //Alterando categoria selecionada
        mudarCategoria: function(id, texto){
            var self = this;
            self.categoriaSelecionada = id;
            for (var index = 0; index < self.categorias.length; index++) {
                var element = self.categorias[index];
                if(element.id===id){
                    element.selecionado=true;
                }else{
                    element.selecionado=false;
                }
                
            }
        },

        //Abrir blocos de itens
        abrirBloco: function(id){
            var self = this;
            self.blocosFechados--;
            self.blocos[id].aberto = true;
            
        },

        //Fechae blocos de itens
        fecharBloco: function(id){
            var self = this;
            self.blocosFechados++;
            self.blocos[id].aberto = false;
        },

        //Permitindo "agarrar"
        allowDrop: function(event){
            event.preventDefault();
        },

        //Ao "agarrar" item
        drag: function(event){

            var self = this;
            var itemId = parseInt(event.target.id.split('_')[1]);
            var indexGravado = null;
            //var faseId = parseInt(event.target.parentNode.id.split('_')[1]);
            for (var index = 0; index < self.listaProjetos.length; index++) {
                var element = self.listaProjetos[index];
                if(element.id===itemId){
                    indexGravado = index;
                }
            }
            self.indexLista = indexGravado;
            self.arrastandoBloco = true;
            self.arrastandoBlocoId = event.target.id;
            self.arrastandoBlocoOrigem = parseInt(event.target.parentNode.id.split('_')[1]);//.getAttribute('origem').split('_')[1];
            event.dataTransfer.setData("text", event.target.id);
           

           
            

        },

        //Ao "soltar" item no destino
        drop: function(event){
            
            var self = this;
            
            //Se for soltar na Lista (vai prara o fim da fila)
            if(event.target.className==='bloco-lista bloco-lista-ativo'){

                event.preventDefault(); // Bloqueia comportamento padrão
                
                var data = event.dataTransfer.getData("text");
                var itemTransferidoId = parseInt(data.split('_')[1]);
                var recebedorId = parseInt(event.target.id.split('_')[1]);
                var indexGravado = self.indexLista;
                var contaRecebedor = 0;
                var permiteContinuar = false;
                var desenvolvedoresTemp = [];


                //Lógica de mudança nos objetos aqui

                // >> A
                //******************************************************************************************************
                //Se vier do bloco Em espera
                if(self.arrastandoBlocoOrigem===0){

                    //Se o domínio NÃO for o demínio pessoal
                    if(self.listaProjetos[indexGravado].dominio!=self.empregadoLogado.matricula){
                       
                        //Se tiver perfil de Administrador do domínio :: Participa ou Indica Participante(s)
                        if(self.empregadoLogado.perfil<=1){

                            //Se já tiver desenvolvedor(es)
                            if(self.listaProjetos[indexGravado].participantes.length===0){
                                var resposta = confirm("Você fará parte da equipe de desenvolvimento do projeto?");
                                if(resposta){
                                    desenvolvedoresTemp.push(self.empregadoLogado.matricula);
                                    //self.listaProjetos[indexGravado].desenvolvedores.push(self.empregadoLogado.matricula);
                                    // >>> Atualiza Tabela Ajax
                                    permiteContinuar = true;
                                }else{
                                    alert('Indique um ou mais integrantes para a equipe!');
                                    //Selecionar Integrantes
                                    permiteContinuar = false;
                                }
                            }else{
                                permiteContinuar = true;
                            }

                        //Se NÃO tiver perfil de Administrador do domínio :: Participa     
                        }else{

                            if(self.listaProjetos[indexGravado].participantes.length===0){
                                desenvolvedoresTemp.push(self.empregadoLogado.matricula);
                                //self.listaProjetos[indexGravado].desenvolvedores.push(self.empregadoLogado.matricula);
                                // >>> Atualiza Tabela Ajax
                                permiteContinuar = true;
                            }else{
                                permiteContinuar = true;
                            }

                        }

                        


                    //Se o domínio for o demínio pessoal :: Participa   
                    }else{

                        if(self.listaProjetos[indexGravado].participantes.length===0){
                            desenvolvedoresTemp.push(self.empregadoLogado.matricula);
                            //self.listaProjetos[indexGravado].desenvolvedores.push(self.empregadoLogado.matricula);
                            // >>> Atualiza Tabela Ajax
                            permiteContinuar = true;
                        }else{
                            permiteContinuar = true;
                        }
                       
                    }
                
                //Se vier de outro bloco
                }else{
                    if(self.listaProjetos[indexGravado].participantes.indexOf(self.empregadoLogado.matricula)!= -1){
                        permiteContinuar = true;
                    }else{
                        alert('Você não está na equipe do projeto!');
                        permiteContinuar = false;
                    }
                }

                // >> B
                //******************************************************************************************************
                
                //Só libera bloco "Em andamento" se já tiver requisitos - VERIFICAR VIABILIDADE
                if(permiteContinuar){
                    if(recebedorId>1){
                        if(self.listaProjetos[indexGravado].listaRequisitos.length===0){
                            alert('Antes de continuar, defina os requisitos do projeto!');
                            permiteContinuar = false;
                        }else{
                            permiteContinuar = true;
                        }
                    }
                }

                // >> C
                //******************************************************************************************************
                
                //Se todos os requisitos forem satisfeitos (permiteContinuar===true)
                if(permiteContinuar){

                    //Se estiver sendo tranferido para Concluído: fecha bloco automaticamente
                    if(recebedorId===3){
                        self.listaProjetos[indexGravado].aberto = false;
                    }

                    
                    //Percorre todos os objetos da lista para recalcular posições
                    for (var index = 0; index < self.listaProjetos.length; index++) {
                        var element = self.listaProjetos[index];
                        //Verifica quantos itens há no bloco para categoria trabalhada
                        if(element.fase == recebedorId && element.categoria===self.categoriaSelecionada){
                            contaRecebedor++;
                        }
                    }

                    //##################################################################################################################################
                    // >>> || Atualizando lista local e Banco de Dados || <<<    

                    //Atualiza Fase
                    self.listaProjetos[indexGravado].fase = recebedorId;
                    // >>> Atualiza Tabela Ajax
                    //Atualiza posição
                    self.listaProjetos[indexGravado].posicao = contaRecebedor-1;
                    // >>> Atualiza Tabela Ajax
                    self.listaProjetos[indexGravado].participantes = self.listaProjetos[indexGravado].participantes.concat(desenvolvedoresTemp);
                    // >>> Atualiza Tabela Ajax

                    // >>> || Fim da Atualização || <<<    
                    //##################################################################################################################################
                    

                    //Ordena por posição
                    self.aplicarOrdem(true, 'posicao', self.listaProjetos);


                }else{
                    //alert('Não foi possível contrinuar!');
                }
           

            //Se for arrastado para trocar de lugar com outro item (NÃO ATUALIZADO AINDA :::: VERIFICAR!!!)
            }else if(event.target.className==='item-arrastar'){
                event.preventDefault();
                var elTrocado = event.target.parentNode;
                var trocadoId = parseInt(elTrocado.id.split("_")[1]);
                var recebedorId = parseInt(elTrocado.parentNode.id.split("_")[1]);

                var data = event.dataTransfer.getData("text");
                var itemTransferidoId = parseInt(data.split('_')[1]);

                var indexGravado = null;
                var posicaoTrocado = null;
                var contaRecebedor = 0;
                //Lógica de mudança nos objetos aqui
                //Percorre todos os objetos da lista 
                for (var index = 0; index < self.listaProjetos.length; index++) {
                    var element = self.listaProjetos[index];
                    
                    if(element.id===itemTransferidoId){
                        self.listaProjetos[index].fase = recebedorId;
                        //Se estiver sendo tranferido para Concluído: fecha bloco automaticamente
                        if(recebedorId===3){
                            self.listaProjetos[index].aberto = false;
                        }
                        //Grava o index do objeto a ser atualizado
                        indexGravado = index;
                        //break;
                    }

                    if(element.id===trocadoId){
                        posicaoTrocado = element.posicao; 
                        console.log(element.id+' >> '+element.posicao);
                    }
               
                }

                //Atualiza posição
                self.listaProjetos[indexGravado].posicao = posicaoTrocado;
                //Atualiza posição do restante da lista    
                for (var index2 = 0; index2 < self.listaProjetos.length; index2++) {
                    var element2 = self.listaProjetos[index2];
                    if(element2.id!==itemTransferidoId && element2.posicao>=posicaoTrocado && element2.categoria===self.categoriaSelecionada){
                        element2.posicao++;
                    }
                    
                    
                }
                
                //Ordena por posição
                self.aplicarOrdem(true, 'posicao', self.listaProjetos);

                //elTrocado.parentNode.insertBefore(document.getElementById(data), elTrocado);

                
                
            }else{
                event.preventDefault();
            }
           


            //Reiniciando "Globais"
            self.indexLista = null;
            self.arrastandoBloco = false;
            self.arrastandoBlocoId = null;
            self.arrastandoBlocoOrigem = null;

        },

        //Redimencionar Item
        redimensionarItem: function(id){
            var self = this;
            for (var index = 0; index < self.listaProjetos.length; index++) {
                var element = self.listaProjetos[index];
                if(element.id === id){
                    element.aberto = !element.aberto;
                }
                
            }
        },


        //Utilitários
        //Ordenar tabela
        aplicarOrdem:function(orderAsc, property, array){
            var self = this;
            array.sort(function(obj1, obj2){
                if(orderAsc){
                    if (obj1[property] < obj2[property]){
                        return -1;
                    }else if (obj1[property] > obj2[property]){
                        return 1;
                    }else{
                        return 0;
                    }
                }else{
                    if (obj1[property] < obj2[property]){
                        return 1;
                    }else if (obj1[property] > obj2[property]){
                        return -1;
                    }else{
                        return 0;
                    } 
                }  
            });

        },


    }

});
