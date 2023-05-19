<div xg-security="$grid_mode, $settings['model:property'], $vars_path, $data" xg-item='<?= $xg_tag ?>' class='qc-xg-item' 
    q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '<?= $vars_post_path ?>', $_qengine_args = null">
    <!-- <div class='sidebar'>
        <div class='sidebar-content'>
            <?= $search_str ?>
        </div>
    </div> -->
    
    @code
        if (!$_qengine_args)
                $_qengine_args = [];
        $_qengine_args['mainData'] = $data;
        if (is_scalar($data))
                $data = \QApi::QueryById("#<?= addslashes(implode(";#", $src_from_types)) ?>;", $data, "<?= qImplodeEntity($selector) ?>");

        $user = \QApi::Call('\Omi\User::GetCurrentUser');
        $dataCls = \QApp::GetDataClass();
        $show_edit = ((($grid_mode !== "add") && ($grid_mode !== "edit")) && (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('edit', '<?= $config['__view__'] ?>', $data))));
        $show_view = false; // ((($grid_mode !== "add") && ($grid_mode !== "view")) && (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('view', '<?= $config['__view__'] ?>', $data))));
        $show_delete = false; // ((($grid_mode !== "add") && ($grid_mode !== "delete")) && (!$dataCls::$_USE_SECURITY_FILTERS || ($user && $user->can('delete', '<?= $config['__view__'] ?>', $data))));
    @endcode
    
    @if (!$this->_is_popup_)
        <!-- 
        page-heading-sticky
        style="margin-top: 60px;"
        -->
        <div class="lg:flex lg:items-center lg:justify-between bg-white shadow p-4 lg:px-8 js-page-heading tpl-page-heading">
            <div class="flex-1 min-w-0">
                <div>
                    <h1 class="text-xl leading-6 font-medium text-gray-700 mb-2 tpl-page-heaeding-title">
                        @code
                            $caption_add = !empty('<?= $addCaption ?>') ? '<?= $addCaption ?>' : null;
                            $caption_edit = !empty('<?= $editCaption ?>') ? '<?= $editCaption ?>' : null;
                            $caption_view_mode = !empty('<?= $viewModeCaption ?>') ? '<?= $viewModeCaption ?>' : null;
                            $caption_delete = !empty('<?= $deleteCaption ?>') ? '<?= $deleteCaption ?>' : null;
                        @endcode

                        @if (($grid_mode === "add") && $caption_add)
                            {{_L($caption_add)}}
                        @elseif (($grid_mode === "edit") && $caption_edit)
                            {{_L($caption_edit)}}
                        @elseif (($grid_mode === "view") && $caption_view_mode)
                            {{_L($caption_view_mode)}}
                        @else
                            {{_L($this->caption ?: '<?= $viewCaption ?>')}}
                        @endif
                    </h1>
                    
                    <!-- BREADCRUMB :: BEGIN -->
                    <div class="tpl-page-breadcrumb mr-auto text-xs hidden sm:flex text-gray-500 items-center"> 
                        <a href="{{$this->url()}}" class="qc-back-btn hover:text-indigo-700">{{_L($this->caption ?: '<?= $viewCaption ?>')}}</a> 
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-3 h-3">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg> 
                        <a href="javascript: void(0);" class="text-indigo-700"><span class="tpl-page-breadcrumb-text _tprimary">{{( $this->show_caption_action ? (($grid_mode === "add") ? " "._L("Add") : (($grid_mode === "edit") ? " "._L("Update") : (($grid_mode === "delete") ? " "._L("Delete") : " "._L("View")))) : "")}}</span></a> 
                    </div>
                    <!-- BREADCRUMB :: END -->
                </div>
            </div>
            <div class="mt-5 flex lg:mt-0 lg:ml-4 tpl-top-actions-buttons">
                <!-- <a href="{{$this->url()}}" class="qc-back-btn btn btn-primary btn-border">
                    &laquo; <span class="_tblack">{{_T('5a312a7b7299e', 'Back')}}</span>
                </a> -->
                @if ($grid_mode !== 'view')
                    @if (($grid_mode === "add") || ($grid_mode === "edit"))
                        <span class="block shadow-sm rounded-md">
                            <a href="javascript: void(0);" class="qc-submit-btn inline-flex items-center px-4 py-2 border border-indigo-300 text-sm leading-5 font-medium rounded-md text-indigo-700 bg-white hover:text-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-indigo-800 active:bg-indigo-50 active:text-indigo-800 transition duration-150 ease-in-out">
                                {{_T('5a2fa73ca20f6', 'Save')}}
                            </a>
                        </span>
                    @elseif ($grid_mode === "delete")
                        <div class="block shadow-sm rounded-md">
                            <a href="javascript: void(0);" class="qc-submit-btn inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                                {{_L("Delete")}}
                            </a>
                        </div>
                    @endif
                @endif
                
                <?php if (!$isPureReference) : ?>
                    @if ($data && $data->getId() && ($show_edit || $show_view || $show_delete))
                        @if ($show_edit)
                            <span class="block shadow-sm rounded-md ml-3">
                                <a href='{{$this->getUrlForTag("id", "edit", $data->getId())}}' class="qc-edit-btn inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                                    {{_L('Edit')}}
                                </a>
                            </span>
                        @endif
                        
                        @if ($show_view)
                            <span class="block shadow-sm rounded-md ml-3">
                                <a href='{{$this->getUrlForTag("id", "view", $data->getId())}}' class="qc-view-btn inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-blue-500 hover:border-blue-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                                    {{_L('View')}}
                                </a>
                            </span>
                        @endif
                        
                        @if ($show_delete)
                            <span class="block shadow-sm rounded-md ml-3">
                                <a href='{{$this->getUrlForTag("id", "delete", $data->getId())}}' class="qc-delete-btn inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-red-500 bg-white hover:text-red-700 hover:border-red-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                                    {{_L('Delete')}}
                                </a>
                            </span>
                        @endif
                    @endif
                <?php endif; ?>
            </div>
        </div>
    @endif
    
    @if (!$this->_is_popup_)
        <div class="mx-auto px-4 sm:px-6 lg:px-8 mt-8">
            <div class="{{(($grid_mode == 'edit') || ($grid_mode == 'add') || ($grid_mode == 'view') || ($grid_mode == 'delete')) ? 'page-body-margin' : ''}}">
    @endif
            
			<?php include(static::GetTemplate("form/form_top_steps.tpl", $config)); ?>
            <div class='qc-grid-properties' data-properties='{{$this->getJsProperties()}}'></div>
            <div class="form-wrapper">
                <form name="<?= $xg_tag ?>" class="xg-form" xg-form='<?= $xg_tag ?>' enctype='multipart/form-data' method='POST' autocomplete='off'>
                    <input type="hidden" value="1" name="__submitted" />
                    @if ($id)
                        <input type="hidden" value="{{$id}}" name="{{$vars_path ? $vars_path.'[Id]' : 'Id'}}" />
                    @else
                        <?= $hiddens ?>
                    @endif
                    <?= $tabs_str ?>
                    @php $cls = (($grid_mode == 'add') || ($grid_mode == 'edit')) ? 'btn-success' : (($grid_mode == 'delete') ? 'btn-alert' : 'btn-warning');
                    @php $caption = ($grid_mode == 'add') ? 'Create' : (($grid_mode == 'delete') ? 'Delete' : 'Save')
                    @if ($grid_mode !== 'view')
                        <!-- <button class="qc-submit-btn btn btn-primary" onclick="return false;">{{_L($caption)}}</button> -->
                    @endif
                </form>
            </div>
                    
    @if (!$this->_is_popup_)
            </div>
        </div>
    @else
        @if ($grid_mode !== 'view')
            @if (($grid_mode === "add") || ($grid_mode === "edit"))
                <span class="block shadow-sm rounded-md">
                    <a href="javascript: void(0);" class="qc-submit-btn flex justify-center mb-4 items-center px-4 py-2 border text-sm leading-5 font-medium rounded-md text-white bg-indigo-700 hover:bg-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-white active:bg-indigo-500 active:text-white transition duration-150 ease-in-out">
                        {{_T('5a2fa73ca20f6', 'Save')}}
                    </a>
                </span>
            @endif
        @endif
    @endif
</div>