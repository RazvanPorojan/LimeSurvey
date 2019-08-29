<?php
    /**
     * @var $this              AdminController
     * @var $oQuestionGroup    QuestionGroup
     * @var $oSurvey           Survey
     * @var $oQuestion         Question
     * @var $ajaxDatas         array
     * @var $copying           boolean
     * @var $adding            boolean
     * @var $aqresult          Question[]
     * @var $aQuestionTypeList array
     * @var $questionType      QuestionTheme
     * @var $oQuestion         Question
     * @var $jsData            array
     * @var $selectedQuestion  array
     * @var $oQuestionSelector PreviewModalWidget
     * @var $this              AdminController
     * TODO: move logic from the view to controller
     */

    // DO NOT REMOVE This is for automated testing to validate we see that page
    echo viewHelper::getViewTestTag('addQuestion');

?>

<?php
    $aQuestionTypeGroups = array();
    $question_template_preview = \LimeSurvey\Helpers\questionHelper::getQuestionThemePreviewUrl($oQuestion->type);
    $selected = null;

    foreach ($aQuestionTypeList as $key => $questionType) {
        $htmlReadyGroup = str_replace(' ', '_', strtolower($questionType['group']));
        if (!isset($aQuestionTypeGroups[$htmlReadyGroup])) {
            $aQuestionTypeGroups[$htmlReadyGroup] = array(
                'questionGroupName' => $questionType['group']
            );
        }
        $imageName = $key;
        if ($imageName == ":") {
            $imageName = "COLON";
        } else {
            if ($imageName == "|") {
                $imageName = "PIPE";
            } else {
                if ($imageName == "*") {
                    $imageName = "EQUATION";
                }
            }
        }

        $questionType['detailpage'] = '
    <div class="col-sm-12 currentImageContainer">
        <img src="' . Yii::app()->getConfig('imageurl') . '/screenshots/' . $imageName . '.png" />
    </div>';
        if ($imageName == 'S') {
            $questionType['detailpage'] = '
        <div class="col-sm-12 currentImageContainer">
            <img src="' . Yii::app()->getConfig('imageurl') . '/screenshots/' . $imageName . '.png" />
            <img src="' . Yii::app()->getConfig('imageurl') . '/screenshots/' . $imageName . '2.png" />
        </div>';
        }
        $aQuestionTypeGroups[$htmlReadyGroup]['questionTypes'][$key] = $questionType;
    }
?>
<?php
    $oQuestionSelector = $this->beginWidget('ext.admin.PreviewModalWidget.PreviewModalWidget', array(
        'widgetsJsName' => "questionTypeSelector",
        'renderType' => (isset($selectormodeclass) && $selectormodeclass == "none") ? "group-simple" : "group-modal",
        'modalTitle' => "Select question type",
        'groupTitleKey' => "questionGroupName",
        'groupItemsKey' => "questionTypes",
        'debugKeyCheck' => "Type: ",
        'previewWindowTitle' => gT("Preview question type"),
        'groupStructureArray' => $aQuestionTypeGroups,
        'value' => $oQuestion->type,
        'debug' => YII_DEBUG,
        'currentSelected' => $selectedQuestion['title'] ?? gT('Invalid Question'),
        'optionArray' => [
            'selectedClass' => $selectedQuestion['settings']->class ?? 'invalid_question',
            'onUpdate' => [
                'value',
                "console.ls.log(value); $('#question_type').val(value); updatequestionattributes(''); updateQuestionTemplateOptions();"
            ]
        ]
    ));
?>
<?= $oQuestionSelector->getModal(); ?>

<?php PrepareEditorScript(true, $this); ?>
<?php $this->renderPartial("./survey/Question/question_subviews/_ajax_variables", $ajaxDatas); ?>

<div id='edit-question-body' class='side-body <?php echo getSideBodyClass(false); ?>'>

    <!-- Page Title-->
    <div class="pagetitle h3">
        <?php
            if ($adding) {
                eT("Add a new question");
            } elseif ($copying) {
                eT("Copy question");
            } else {
                eT("Edit question");
                echo ': <em>' . $oQuestion->title . '</em> (ID:' . $oQuestion->qid . ')';
            }
        ?>
    </div>

    <div class="row">
        <!-- Form for the whole page-->
        <?php echo CHtml::form(array("admin/database/index"), 'post', array('class' => 'form30 ', 'id' => 'frmeditquestion', 'name' => 'frmeditquestion')); ?>
        <!-- The tabs & tab-fanes -->
        <div class="col-sm-12 col-md-7 content-right">
            <?php if ($adding): ?>
                <?php
                $this->renderPartial(
                    './survey/Question/question_subviews/_tabs',
                    array(
                        'oSurvey' => $oSurvey,
                        'oQuestion' => $oQuestion,
                        'surveyid' => $oSurvey->sid,
                        'gid' => $oQuestion->gid,
                        'qid' => null,
                        'adding' => $adding,
                        'aqresult' => $aqresult,
                        'action' => $action
                    )
                ); ?>
            <?php else: ?>
                <?php
                $this->renderPartial(
                    './survey/Question/question_subviews/_tabs',
                    array(
                        'oSurvey' => $oSurvey,
                        'oQuestion' => $oQuestion,
                        'surveyid' => $oSurvey->sid,
                        'gid' => $oQuestion->gid,
                        'qid' => $oQuestion->qid,
                        'adding' => $adding,
                        'aqresult' => $aqresult,
                        'action' => $action
                    )
                ); ?>

            <?php endif; ?>
        </div>

        <!-- The Accordion -->
        <div class="col-sm-12 col-md-5" id="accordion-container" style="background-color: #fff; z-index: 2;">
            <?php // TODO : find why the $groups can't be generated from controller?>
            <div id='questionbottom'>
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    <!-- Copy options -->
                    <?php if ($copying): ?>
                        <div class="panel panel-default">
                            <div class="panel-heading" role="tab" id="heading-copy">
                                <a class="panel-title h4 selector--questionEdit-collapse" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-copy" aria-expanded="false" aria-controls="collapse-copy">
                                    <?php eT("Copy options"); ?>
                                </a>
                            </div>
                            <div id="collapse-copy" class="panel-collapse collapse  in" role="tabpanel" aria-labelledby="heading-copy">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label class=" control-label" for='copysubquestions'><?php eT("Copy subquestions?"); ?></label>
                                        <div class="">
                                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                                'name' => 'copysubquestions',
                                                'id' => 'copysubquestions',
                                                'value' => 'Y',
                                                'onLabel' => gT('Yes'),
                                                'offLabel' => gT('No')
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class=" control-label" for='copyanswers'><?php eT("Copy answer options?"); ?></label>
                                        <div class="">
                                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                                'name' => 'copyanswers',
                                                'id' => 'copyanswers',
                                                'value' => 'Y',
                                                'onLabel' => gT('Yes'),
                                                'offLabel' => gT('No')
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class=" control-label" for='copydefaultanswers'><?php eT("Copy default answers?"); ?></label>
                                        <div class="">
                                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                                'name' => 'copydefaultanswers',
                                                'id' => 'copydefaultanswers',
                                                'value' => 'Y',
                                                'onLabel' => gT('Yes'),
                                                'offLabel' => gT('No')
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class=" control-label" for='copyattributes'><?php eT("Copy advanced settings?"); ?></label>
                                        <div class="">
                                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                                'name' => 'copyattributes',
                                                'id' => 'copyattributes',
                                                'value' => 'Y',
                                                'onLabel' => gT('Yes'),
                                                'offLabel' => gT('No')
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; // Copying ?>

                    <!-- General Options -->
                    <div class="panel panel-default" id="questionTypeContainer">
                        <!-- General Options : Header  -->
                        <div class="panel-heading" role="tab" id="headingOne">
                            <a class="panel-title h4 selector--questionEdit-collapse" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-question" aria-expanded="true" aria-controls="collapse-question">
                                <?php eT("General options"); ?>
                            </a>
                        </div>

                        <div id="collapse-question" class="panel-collapse collapse <?php if (!$copying) {
                            echo ' in ';
                        } ?>" role="tabpanel" aria-labelledby="headingOne">
                            <div class="panel-body">
                                <!-- Question selector start -->
                                <?php //Question::getQuestionTypeName($eqrow['type']); ?>
                                <?php //elseif($activated != "Y" && (isset($selectormodeclass) && $selectormodeclass == "none")): ?>


                                <div class="form-group">
                                    <label class=" control-label" for="question_type_button">
                                        <?php
                                            eT("Question type:");
                                        ?>
                                    </label>
                                    <?php if (!$oSurvey->isActive) : ?>
                                        <?= $oQuestionSelector->getButtonOrSelect(); ?>
                                        <input type="hidden" id="question_type" name="type" value="<?php echo $oQuestion->type; ?>"/>
                                    <?php elseif ($oSurvey->isActive) : ?>
                                        <input type="hidden" id="question_type" name="type" value="<?php echo $oQuestion->type; ?>"/>
                                        <!-- TODO : control if we can remove, disable update type must be done by PHP -->
                                        <div class=" btn-group" id="question_type_button">
                                            <button type="button" class="btn btn-default" disabled aria-haspopup="true" aria-expanded="false">
                                                <span class="buttontext" id="selector__editView_question_type_description">
                                                    <?= $selectedQuestion['title'] ?? gT('Invalid Question'); ?>
                                                    <?php if (YII_DEBUG): ?>
                                                        <em class="small">
                                                            Type code: <?php echo $oQuestion->type; ?>
                                                        </em>
                                                    <?php endif; ?>
                                                </span>
                                                &nbsp;&nbsp;&nbsp;
                                                <i class="fa fa-lock"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php $this->endWidget('ext.admin.PreviewModalWidget.PreviewModalWidget'); ?>
                                <!-- Question selector end -->

                                <div class="form-group" id="QuestionTemplateSelection">
                                    <label class=" control-label" for='question_template'>
                                        <?php eT("Question theme:"); ?>
                                        <a class="text-primary show-help" data-toggle="collapse" href="#help_question_template" aria-expanded="false" aria-controls="help_question_template" aria-hidden=true>
                                            <span class="fa fa-info-circle"></span>
                                        </a>
                                    </label>
                                    <p class="help-block collapse" id="help_question_template"><?php eT("Use a customized question theme for this question"); ?></p>
                                    <select id="question_template" name="question_template" class="form-control">
                                        <?php
                                            foreach ($aQuestionTemplateList as $code => $value) {
                                                if (!empty($aQuestionTemplateAttributes) && isset($aQuestionTemplateAttributes['value'])) {
                                                    $question_template_preview = $aQuestionTemplateAttributes['value'] == $code ? $value['preview'] : $question_template_preview;
                                                    $selected = $aQuestionTemplateAttributes['value'] == $code ? 'selected' : '';
                                                }
                                                if (YII_DEBUG) {
                                                    echo sprintf("<option value='%s' %s>%s (code: %s)</option>", $code, $selected, CHtml::encode($value['title']), $code);
                                                } else {
                                                    echo sprintf("<option value='%s' %s>%s</option>", $code, $selected, CHtml::encode($value['title']));
                                                }
                                            }
                                        ?>
                                    </select>
                                    <div class="help-block" id="QuestionTemplatePreview">
                                        <strong><?php eT("Preview:"); ?></strong>
                                        <div class="">
                                            <img src="<?php echo $question_template_preview; ?>" class="img-thumbnail img-responsive center-block">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class=" control-label" for='gid'><?php eT("Question group:"); ?></label>
                                    <div class="">
                                        <select name='gid' id='gid' class="form-control" <?php if ($oSurvey->isActive) {
                                            echo " disabled ";
                                        } ?> >
                                            <?php echo getGroupList3($oQuestion->gid, $oSurvey->sid); ?>
                                        </select>
                                        <?php if ($oSurvey->isActive): ?>
                                            <input type='hidden' name='gid' value='<?php echo $oQuestion->gid; ?>'/>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group" id="OtherSelection">
                                    <label class=" control-label" for="other"><?php eT("Option 'Other':"); ?></label>
                                    <div class="">
                                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => 'other',
                                            'id' => 'other',
                                            'value' => $oQuestion->other === "Y",
                                            'onLabel' => gT('On'),
                                            'offLabel' => gT('Off'),
                                            'htmlOptions' => array(
                                                'disabled' => $oSurvey->isActive,
                                                'value' => 'Y',
                                                'uncheckValue' => 'N',
                                            ),
                                        ));
                                            if ($oSurvey->isActive) {
                                                echo CHtml::hiddenField('other', $oQuestion->other);
                                            }
                                        ?>
                                    </div>
                                </div>

                                <div id='MandatorySelection' class="form-group">
                                    <label class=" control-label" for="mandatory"><?php eT("Mandatory:"); ?>
                                        <a class="text-primary show-help" data-toggle="collapse" href="#help_mandatory" aria-expanded="false" aria-controls="help_mandatory" aria-hidden=true>
                                            <span class="fa fa-info-circle"></span>
                                        </a>
                                    </label>
                                    <p class="help-block collapse" id="help_mandatory"><?php eT("Set \"Mandatory\" state. Use \"Soft\" option to allow question to be skipped."); ?></p>
                                    <div class="">
                                        <!-- Todo : replace by direct use of bootstrap switch. See statistics -->
                                        <?php
                                            $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                                                'name' => 'mandatory',
                                                'value' => $oQuestion->mandatory,
                                                'selectOptions' => array(
                                                    "Y" => gT("Yes", 'unescaped'),
                                                    "S" => gT("Soft", 'unescaped'),
                                                    "N" => gT("No", 'unescaped')
                                                )
                                            ));
                                        ?>
                                    </div>
                                </div>

                                <div class="form-group" id="EncryptedSelection">
                                    <label class=" control-label" for="encrypted"><?php eT("Encrypted:"); ?></label>
                                    <div class="">
                                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                            'name' => 'encrypted',
                                            'id' => 'encrypted',
                                            'value' => $oQuestion->encrypted === "Y",
                                            'onLabel' => gT('On'),
                                            'offLabel' => gT('Off'),
                                            'htmlOptions' => array(
                                                'disabled' => $oSurvey->isActive,
                                                'value' => 'Y',
                                                'uncheckValue' => 'N',
                                            ),
                                        )); ?>
                                    </div>
                                </div>

                                <div class="form-group" id="relevanceContainer">
                                    <label class=" control-label" for='relevance'>
                                        <?php eT("Relevance equation:"); ?>
                                        <a class="text-primary show-help" data-toggle="collapse" href="#help_relevance" aria-expanded="false" aria-controls="help_relevance" aria-hidden=true>
                                            <span class="fa fa-info-circle"></span>
                                        </a>
                                    </label>
                                    <div class="help-block collapse" id="help_relevance">
                                        <p>
                                            <?php eT("The relevance equation can be used to add branching logic. This is a rather advanced topic. If you are unsure, just leave it be."); ?><br>
                                            <a href="https://manual.limesurvey.org/Expression_Manager" rel="help"><?php eT("More on relevance and the Expression Manager."); ?></a>
                                        </p>
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">{</div>
                                        <?php echo CHtml::textArea('relevance', $oQuestion->relevance,
                                            array('id' => 'relevance', 'class' => "form-control", 'readonly' => count($oQuestion->conditions) > 0)
                                        ); ?>
                                        <div class="input-group-addon">}</div>
                                    </div>
                                    <?php if (count($oQuestion->conditions) > 0) : ?>
                                        <div class='help-block text-warning'> <?php eT("Note: You can't edit the relevance equation because there are currently conditions set for this question."); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div id='Validation' class="form-group">
                                    <label class=" control-label" for='preg'><?php eT("Validation:"); ?></label>
                                    <div class="">
                                        <?php echo CHtml::textField('preg', $oQuestion->preg,
                                            array('class' => "form-control", 'id' => 'preg', 'size' => 50)
                                        ); ?>
                                    </div>
                                </div>
                                <?php if ($adding || $copying): ?>
                                    <!-- Rendering position widget -->
                                    <?php $this->widget('ext.admin.survey.question.PositionWidget.PositionWidget', array(
                                        'display' => 'ajax_form_group',
                                        'oQuestionGroup' => $oQuestionGroup,
                                    ));
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!$copying): ?>
                        <div id="container-advanced-question-settings" class="custom custom-margin top-5">
                            <input type='hidden' name='advancedquestionsettingsLoaded' value=''/>
                            <div class="panel"></div>
                            <!-- Advanced settings -->
                        </div>
                        <div class="loader-advancedquestionsettings text-center">
                            <div class="contain-pulse animate-pulse">
                                <div class="square"></div>
                                <div class="square"></div>
                                <div class="square"></div>
                                <div class="square"></div>
                            </div>
                            <!-- <span class="fa fa-refresh fa-spin" style="font-size:3em;" aria-hidden='true'></span> -->
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($adding): ?>
            <input type='hidden' name='action' value='insertquestion'/>
            <input type='hidden' id='sid' name='sid' value='<?= $oSurvey->sid; ?>'/>
            <p><input type='submit' class="hidden" value='<?php eT("Add question"); ?>'/></p>
            <div id="loader" class="blocker-loading">
                <div class="blocker-loading-container"><i class="loading-icon-fa fa fa-cog fa-spin"></i></div>
            </div>
        <?php elseif ($copying): ?>
            <input type='hidden' name='action' value='copyquestion'/>
            <input type='hidden' id='oldqid' name='oldqid' value='<?= $oQuestion->qid; ?>'/>
            <p><input type='submit' class="hidden" value='<?php eT("Copy question"); ?>'/></p>
        <?php else: ?>
            <input type='hidden' name='action' value='updatequestion'/>
            <input type='hidden' id='qid' name='qid' value='<?= $oQuestion->qid; ?>'/>
            <p>
                <button type='submit' class="saveandreturn hidden" name="redirection" value="edit"><?php eT("Save") ?> </button>
            </p>
            <input type='submit' class="hidden" value='<?php eT("Save and close"); ?>'/>
        <?php endif; ?>
        <input type='hidden' name='sid' value='<?= $oSurvey->sid; ?>'/>
        <?= CHtml::endForm() ?>
        <div id='questionactioncopy' class='extra-action'>
            <button type='submit' class="btn btn-primary saveandreturn hidden" name="redirection" value="edit"><?php eT("Save") ?> </button>
            <input type='submit' value='<?php eT("Save and close"); ?>' class="btn btn-default hidden"/>
        </div>

    </div>
</div>

