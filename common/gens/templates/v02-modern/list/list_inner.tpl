<virtual q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
    <div class="lg:flex lg:items-center lg:justify-between bg-white shadow p-4 lg:px-8 js-page-heading">
        <div class="flex-1 min-w-0">
            <h1 class="text-xl leading-6 font-medium text-cool-gray-900 tpl-page-heading-title">
                @if ($caption_list)
                    {{_L($caption_list)}}
                @else
                    {{_L($this->caption ?: '<?= $viewCaption ?>')}}
                @endif
            </h1>
        </div>

		<?php include(static::GetTemplate("list_top_actions.tpl", $config)) ?>
    </div>
                
    <div class="mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        @code
            $caption_list = !empty('<?= $listCaption ?>') ? '<?= $listCaption ?>' : null;
        @endcode

        @php $limit_offset = $data ? count($data) : 0;
        @php $qc = $data ? $data->getQueryCount() : 0;
        <div class="qc-main-list">
            <div class="flex flex-col mt-2" style="transform: translateZ(0);">
                <div xg-list="<?= $xg_tag ?>" class="align-middle min-w-full overflow-x-auto shadow overflow-hidden sm:rounded-lg js-sortable qc-list-vars-path-index-container">
                    <!-- 
                        @CLASS
                            table-order - used for css in order to show order arrow asc desc
                    -->
                    <table class="min-w-full divide-y divide-cool-gray-200 js-itms-table css-items-table table-order">
                        <thead class="divide-y">
						<tr class="table-heading-search js-search-fields-row">
							<?php if ($_TYPE_FLAGS['list_checkboxes']): 
							?><th class='qc-chk-heading'>
								<strong>
									<div class='qc-checkbox'>
										<input class='qc-chkcollitm-pick-bulk' type='checkbox' id='{{$unk = uniqid()}}' />
										<label for={{$unk}}></label>
									</div>
								</strong>
							</th><?php endif; ?>
                                <th class="qc-pos-heading bg-cool-gray-50 sticky top-0 z-1">&nbsp;
                                    <?php
                                        foreach ($_oby_data ?: [] as $oby)
                                        {
                                            list($input_name, $bind_val_index) = $oby; 

                                            ?>
                                                <input sync-identifier='<?= $input_name ?>' class='js-oby-field js-keepin-sync' type='hidden' name='<?= $input_name ?>' oby-indx="<?= $input_name ?>" value='{{$bind_params<?= $bind_val_index ?>}}' />
                                            <?php
                                        }
                                    ?>

                                    <input type='hidden' class='js-limit-field js-limit-offset' name='LIMIT[]' value='{{$limit_offset}}' />
                                    <input type='hidden' class='js-limit-field js-limit-length' name='LIMIT[]' value='{{$this->rowsOnPage}}' />
                                </th>
                                
                                <?php
                                    /*
                                     |--------------------------------------------------------------------------------------------------------------
                                     | ATTETION :: do not change into @if, because this is used for generating not render
                                     |--------------------------------------------------------------------------------------------------------------
                                     */
                                    foreach ($_headings_data ?: [] as $headingData)
                                    {
                                        list($label, $caption, $cssCls, $oby, $h_prop, $_finfo, $h_search_data, $h_qsearch_data) = $headingData;
                                        ?>
								
                                            <th xg-security="$grid_mode, $settings['model:property'], $vars_path" xg-security-property='<?= $h_prop->name ?>' <?= $oby ? " data-order='{$oby}'" : "" ?> class="qc-heading px-6 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1<?= ($oby ? " js-order-by order{{\$bind_params['{$oby}'] ? ' ' . strtolower(\$bind_params['{$oby}']) : ''}}" : "").$cssCls ?>" xg-property-label='<?= $label ?>'>
                                                <?php 												
							$render_head = true;
							if ($h_prop->storage && $h_prop->storage['admin.render_IF'])
								$render_head = $h_prop->storage['admin.render_IF'];
													
							if ($render_head !== true)
								echo "\n@if (".$render_head.")\n";
													
                                                    if ($h_qsearch_data && $h_qsearch_data[1])
                                                    {
                                                        echo $h_qsearch_data[1];
                                                    }
                                                    else
                                                    {
                                                        ?>
                                                            <div class="table-heading-caption">{{_L('<?= $caption ?>')}}</div>
                                                        <?php
                                                    } 
													
							if ($render_head !== true)
								echo "\n@endif\n";
                                                ?>
                                            </th>
                                        <?php
                                    }
                                ?>
                                <th class="qc-action-heading bg-cool-gray-50 sticky top-0 z-1">
                                    <strong>&nbsp;</strong>
                                </th>
                            </tr>
                        </thead>

                        @php $item_k_max = -1;
                        @php $pos = 0;

                        @if ($data && count($data) > 0)
                            @each ($data as $item_k => $item)
                                @php $pos++;
                                @include(<?= $dd_include_method ?>, $settings, $item, $bind_params, $grid_mode, $id, "", ["_rowi" => [$vars_path."[_rowi][{$item_k}]", $data->_rowi[$item_k]], "_k" => $item_k, "mainData" => $_qengine_args ? $_qengine_args['mainData'] : null, "_pos" => $pos, "_tsp" => [$vars_path."[_tsp][{$item_k}]"]]);
                                @php $item_k_max = max($item_k_max, $item_k);
                            @end
                            @php $item_k_max++;
                        @end

                        @php $item_k_max++;

                        @if (!$data || (count($data) === 0))
                            @include(<?= $noResultsMethod ?>)
                        @endif

                        <tbody class="qc-list-vars-path-index-row js-prepend-search-results bg-white divide-y divide-cool-gray-200"  style='display: none;'>
                            <tr class='qc-list-vars-path-index' data-next-crt-no='{{isset($pos) ? ++$pos : 1}}' data-form-vars-path-index='{{$item_k_max}}'>
                                <td colspan="100%"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                                                    
                @if (true) /* count($data) >= $this->rowsOnPage */
                    <div class='qc-collection-more-wrapper'>
                        <button onclick="javascript: void(0);" class="qc-collection-more mt-4 inline-flex items-center px-4 py-2 border border-indigo-300 text-sm leading-5 font-medium rounded-md text-indigo-700 bg-white hover:text-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-indigo-800 active:bg-indigo-50 transition duration-150 ease-in-out" 
                            data-offset='{{$data ? count($data) : 0}}' 
                            data-length='{{$this->rowsOnPage}}' 
                            data-from='{{$this->from}}'
                            data-selector='{{$this->getSelectorForMode($this->grid_mode)}}'
                            data-grid-mode='{{$this->grid_mode}}'
                            data-parent-prefix-url='{{$this->parentPrefixUrl}}'
                            data-form-vars-path='{{$vars_path ?: ""}}' 
                            data-form-render="<?= $add_render ?>">
                            {{_L('More')}}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</virtual>