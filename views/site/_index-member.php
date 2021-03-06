<?php 
use yii\helpers\Html;
use app\assets\VueMultiselectAsset;
use app\assets\VueToastedAsset;
use app\assets\AxiosAsset;
use app\widgets\FontAwesome;
/**
 * @var $this yii\web\View
 * @var $form app\models\StatusForm
 */
VueToastedAsset::register($this);
VueMultiselectAsset::register($this);
AxiosAsset::register($this);

$this->registerCss('
[v-cloak] {display: none;}

.heading {
    color: #999;
    font-size: .8rem;
}
.l-status-form textarea {
    height: 20vh;
    min-height: 5rem;
}
.l-select-program .nav .nav-item {
    font-size: .9rem;
}
.l-select-program .nav .nav-link {
    padding: .5rem .75rem;
}
.l-select-program .nav .nav-link label {
    cursor: pointer;
}
.l-select-program .nav .nav-link label:hover {
    opacity: .65;
    transition: .25s all linear;
}
');

$this->registerJs('
// @ref https://vue-multiselect.js.org/#sub-tagging
Vue.use(Toasted);

new Vue({
    el: "#status-form",
    components: {VueMultiselect: window.VueMultiselect.default},
    data(){
        return '.json_encode([
            'status' => $form->status,
            'tags' => $form->tags,
            'options' => $tags,
            'nitiasaPrograms' => Yii::$app->params['nitiasaPrograms'],
            'currentProgram' => $currentProgram,
        ]).'
    },
    methods: {
        setProgram (key, event) {
            this.currentProgram = key;
        },
        addTag (newTag) {
            if (newTag.slice(0,1) != "#") {
                newTag = "#" + newTag
            }
            this.options.push(newTag)
            this.tags.push(newTag)
        }, 
        submit (event) {
            let form = event.target;
            let params = new FormData();
            let toastOptions = {
                position: "bottom-center",
                duration: 3000,
                type: "success",
                fitToScreen: false,
                fullWidth: true,
                action: {
                    text : "CLOSE",
                    onClick : (e, toastObject) => {
                        toastObject.goAway(0);
                    }
                }
            };

            params.append("status", this.status);
            params.append("tags", this.tags);
            axios.post("/status", params)
            .then(function(response) {
                form.reset();
                this.status = "";
                toastOptions.type = "success";
                Vue.toasted.show("投稿が完了しました", toastOptions);
                console.log(response);
            })
            .catch(function (error) {
                toastOptions.type = "error";
                Vue.toasted.show("投稿に失敗しました", toastOptions);
                console.log(error)
            });
        },
    }
});
', $this::POS_END);
?>
<div class="row">
    <div class="col-md-5 mx-auto">
        <div class="l-status-form">
            <?= Html::beginForm(NULL, NULL, ['id' => 'status-form', '@submit.prevent' => 'submit']) ?>
                <?= Html::errorSummary($form) ?>
                <div class="form-group">
                    <?= Html::activeTextarea($form, 'status', ['class' => 'form-control', 'placeholder' => '実況してみよう！', 'v-model' => 'status']) ?>
                </div>
                <div class="form-group d-flex">
                    <div class="ml-auto">
                        <button type="submit" class="btn btn-danger">つぶやく！</button>
                    </div>
                </div>
                <div class="l-select-program form-group">
                    <h6 class="heading">番組を選ぶ</h6>
                    <div class="nav nav-pills nav-justified mb-3">
                        <div v-for="(program, key) in nitiasaPrograms" class="nav-item nav-link" :class="{active: key == currentProgram}">
                            <label :for="'program-' + key" @click="setProgram(key, $event)" class="mb-0 py-1" v-cloak>
                                <?= FontAwesome::widget(['icon' => 'hashtag']) ?>{{key}}
                            </label>
                            <input name="programs" type="radio" :id="'program-' + key" :value="program.tags" v-model="tags" hidden>
                        </div>
                    </div>
                    <h6 class="heading">タグをカスタマイズする</h6>
                    <div id="input-tag">
                        <vue-multiselect v-model="tags" :options="options" :multiple="true" :taggable="true" @tag="addTag" placeholder="自分でタグを入力する"></multiselect>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
