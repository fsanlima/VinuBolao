<template>
    <div>
        <div class="col-sm-12 box" v-if="!user && jogos.length == 0">
            <div class="alert alert-danger">
                <p class="text-center">Não existe dados para listar!</p>
            </div>
        </div>
        <div v-else="">
            <div class="col-sm-12 box">
                <div class="btn-group" role="group">
                    <form class="form-inline">
                        <div class="form-group">
                            <select id="infoCampeonato" class="form-control" v-model="campeonato.id" @change="getPalpites(user.user_id, campeonato.id, rodada);">
                                <option v-for="campeonato in campeonatos" :value="campeonato.id">{{ campeonato.nome_completo }}</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default" :disabled="rodada < 2" @click="getPalpites(user.user_id, campeonato.id, rodada - 1);">
                        <span class="glyphicon glyphicon-menu-left" aria-hidden="true"></span>
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ rodada }}ª Rodada <span class="caret"></span>
                        </button>
                        <ol class="dropdown-menu dropdown-rodada-ol">
                            <li class="dropdown-rodada-li" v-for="n in 38">
                                <a @click="getPalpites(user.user_id, campeonato.id, n);" class="dropdown-rodada-a">{{ n }}ª</a>
                            </li>
                        </ol>
                    </div>
                    <button type="button" class="btn btn-default" :disabled="rodada >= campeonato.qtd_rodada" @click="getPalpites(user.user_id, campeonato.id, rodada + 1);">
                        <span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            <div class="col-sm-12 box" v-if="dataLoading">
                <i class="glyphicon glyphicon-refresh"></i> Loading...
            </div>
            <div v-if="!dataLoading">
                <div class="col-sm-12 box">
                    <div v-if="saveLoading" style="margin-bottom: 5px;">
                        <i class="glyphicon glyphicon-refresh"></i> Loading...
                    </div>
                    <table class="table">
                        <tr class="tr-head">
                            <th class="text-center">Status</th>
                            <th colspan="3" class="text-center">Palpites </th>
                            <th class="text-center">Editar</th>
                        </tr>
                        <tr v-for="(jogo, key) in jogos">
                            <td class="text-center">
                                <span class="glyphicon glyphicon-remove" aria-hidden="true" v-if="jogo.placar_casa === null && jogo.placar_fora === null"></span>
                                <span class="glyphicon glyphicon-ok" aria-hidden="true" v-else></span>
                            </td>
                            <td class="text-right">
                                <span class="hidden-xs">{{ jogo.timecasa.nome }}</span>
                                <span class="hidden-sm hidden-md hidden-lg">{{ jogo.timecasa.sigla }}</span>
                            </td>
                            <td class="td-jogo">
                                <input class="input-placar" type="number" :disabled="saveLoading" min="0" v-if="jogo.placar_casa === null" v-model="jogo.palpite.casa" @blur="savePalpite(jogo);">
                                <strong class="placar-casa" v-else>{{ jogo.placar_casa }}</strong>
                                x
                                <input class="input-placar" type="number" :disabled="saveLoading" min="0" v-if="jogo.placar_fora === null" v-model="jogo.palpite.fora" @blur="savePalpite(jogo);">
                                <strong class="placar-fora" v-else>{{ jogo.placar_fora }}</strong>
                            </td>
                            <td class="text-left">
                                <span class="hidden-xs">{{ jogo.timefora.nome }}</span>
                                <span class="hidden-sm hidden-md hidden-lg">{{ jogo.timefora.sigla }}</span>
                            </td>
                            <td class="text-center">
                                <div v-show="!saveLoading">
                                    <a href="" v-if="jogo.placar_casa !== null || jogo.placar_fora !== null" @click.prevent="savePalpite(jogo, true)">
                                        <span class="glyphicon glyphicon-edit" aria-hidden="true" v-if="jogo.placar_casa !== null || jogo.placar_fora !== null"></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                dataLoading: false,
                saveLoading: false,
                user: (this.users) ? JSON.parse(this.users) : null,
                jogos: [],
                rodada: 1,
                palpites: [],
                campeonatos: [],
                campeonato: {}
            }
        },
        props: ['users'],
        mounted() {
            if(this.user) this.getCampeontatos();
        },
        methods: {
            getCampeontatos(id) {
                this.dataLoading = true;
                const param = (id) ? '/' + id : '';
                this.$http.get('/api/campeonato/get' + param).then((response) => {
                    if(id){
                        this.campeonato = response.data;
                    } else {
                        this.campeonatos = response.data;
                        this.campeonato = this.campeonatos[0];
                        this.rodada = this.campeonato.rodada;

                        this.getPalpites(this.user.user_id, this.campeonato.id, this.rodada);
                    }

                    //Remove Loading
                    this.dataLoading = false;
                }).catch((error) => {
                    console.error('!Get Campeonatos', error);
                });
            },

            getPalpites(userId, campeonatoId, rodada) {
                this.saveLoading = true;
                this.$http.get('/api/palpite/getPalpites/' + userId + '/' + campeonatoId + '/' + rodada).then((response) => {
                    response.data.forEach(function (jogo) {
                        jogo.palpite = {
                            casa: null,
                            fora: null
                        }
                    });

                    this.jogos = response.data;
                    this.rodada = rodada;

                    //Remove Loading
                    this.dataLoading = false;
                    this.saveLoading = false;
                }).catch((error) => {
                    console.error('!Get JogosCampeonato', error);
                });
            },

            savePalpite(jogo, edit) {
                if( (edit) || (jogo.palpite.casa !== null && jogo.palpite.fora !== null) ){
                    this.saveLoading = true;
                    jogo.placar_casa = (jogo.placar_casa === null) ? jogo.palpite.casa : null;
                    jogo.placar_fora = (jogo.placar_fora === null) ? jogo.palpite.fora : null;

                    jogo.userId = this.user.user_id;

                    this.$http.post('/api/palpite/save', jogo).then((response) => {
                        this.getPalpites(this.user.user_id, this.campeonato.id, this.rodada);
                    }).catch((error) => {
                        console.error('!Save Palpite', error);
                    });
                }
            }
        }
    }
</script>

<style>
    .tr-head {
        background-color: #666;
        color: #fff;
    }
    .input-placar {
        width: 35px;
    }
    .td-jogo {
        width: 100px;
        text-align: center;
    }
    .placar-casa, .placar-fora {
        font-size: 20px;
    }
    .placar-casa {
        padding-right: 11px;
    }
    .placar-fora {
        padding-left: 10px;
    }
    .glyphicon.glyphicon-remove {
        color: #d9534f;
    }
    .glyphicon.glyphicon-ok {
        color: #398439;
    }
    .dropdown-rodada-ol{
        left: -39px;
        min-width: 170px;
        height: 150px;
        overflow-y: auto;
    }
    .dropdown-rodada-li{
        display: inline-table;
        border: 1px solid #cccecf;
        width: 30%;
        margin-left: 4px;
        margin-bottom: 5px;
        padding: 0;
        font-size: 14px;
        border-radius: 2px;
    }
    .dropdown-rodada-a {
        padding: 5px !important;
        text-align: center;
        cursor: pointer;
    }
</style>
