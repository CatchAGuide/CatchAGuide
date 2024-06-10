<div class="modal fade" id="guidingModal{{$guiding->id}}" tabindex="-1" role="dialog" aria-labelledby="guidingModal{{$guiding->id}}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" style="padding-right: 40px; padding-bottom: 20px"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <a href="{{ route('guidings.show', [$guiding->id,$guiding->slug]) }}" >
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img class=""
                             src="{{asset('images/' . $guiding->thumbnail_path)}}"
                             alt="" style="width: 100%; height: 500px; object-fit: cover;">

                    </div>
                    <div class="col-md-12">
                        <h3 class="tour-details-two__title">{{$guiding->title}}</h3>
                        <ul class="list-unstyled tour-details__top-list">
                            <li>
                                <div class="icon">
                                    <span class="icon-clock"></span>
                                </div>
                                <div class="text">
                                    <p>Dauer</p>
                                    {{ two($guiding->duration) }}
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <span class="icon-user"></span>
                                </div>
                                <div class="text">
                                    <p>Max. Gäste</p>
                                    {{$guiding->max_guests}}
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <span class="icon-place"></span>
                                </div>
                                <div class="text">
                                    <p>Ort</p>
                                    {{$guiding->location}}
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="about-one__right mt-5 pt-2" style="margin-left: 0px;">
                            <ul class="list-unstyled tour-details-two__overview-bottom-list">
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Zielfisch/e:</b>
                                            {{$guiding->target_fish_aal ? 'AAl ' : ""}}
                                            {{$guiding->target_fish_aland ? 'Aland ' : ""}}
                                            {{$guiding->target_fish_aesche ? 'Äsche ' : ""}}
                                            {{$guiding->target_fish_barbe ? 'Barbe ' : ""}}
                                            {{$guiding->target_fish_barsch ? 'Barsch ' : ""}}
                                            {{$guiding->target_fish_brasse ? 'Brasse ' : ""}}
                                            {{$guiding->target_fish_doebel ? 'Döbel ' : ""}}
                                            {{$guiding->target_fish_forelle ? 'Forelle ' : ""}}
                                            {{$guiding->target_fish_hecht ? 'Hecht ' : ""}}
                                            {{$guiding->target_fish_huchen ? 'Huchen ' : ""}}
                                            {{$guiding->target_fish_karpfen ? 'Karpfen ' : ""}}
                                            {{$guiding->target_fish_nase ? 'Nase ' : ""}}
                                            {{$guiding->target_fish_rapfen ? 'Rapfen ' : ""}}
                                            {{$guiding->target_fish_rotauge ? 'Rotauge ' : ""}}
                                            {{$guiding->target_fish_rotfeder ? 'Rotfeder ' : ""}}
                                            {{$guiding->target_fish_schleie ? 'Schleie ' : ""}}
                                            {{$guiding->target_fish_wels ? 'Wels ' : ""}}
                                            {{$guiding->target_fish_zander ? 'Zander ' : ""}}
                                            {{$guiding->target_fish_sonstiges ? $guiding->target_fish_sonstiges : ""}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Angel Art:</b>
                                            {{$guiding->fishing_type}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Technik:</b>
                                            {{$guiding->methods_carolinarig ? 'Carolina-Rig ' : ""}}
                                            {{$guiding->methods_dropshot ? 'Dropshot ' : ""}}
                                            {{$guiding->methods_eisfischen ? 'Eisfischen ' : ""}}
                                            {{$guiding->methods_feederangeln ? 'Feederangeln ' : ""}}
                                            {{$guiding->methods_fliegenfischen ? 'Fliegenfischen ' : ""}}
                                            {{$guiding->methods_grundblei ? 'Grundblei ' : ""}}
                                            {{$guiding->methods_hardbait ? 'Hardbait ' : ""}}
                                            {{$guiding->methods_jerkbaitangeln ? 'Jerkbaitangeln ' : ""}}
                                            {{$guiding->methods_jiggen ? 'Jiggen ' : ""}}
                                            {{$guiding->methods_koederfisch ? 'Köderfisch ' : ""}}
                                            {{$guiding->methods_pose ? 'Pose ' : ""}}
                                            {{$guiding->methods_schleppangeln ? 'Schleppangeln ' : ""}}
                                            {{$guiding->methods_texasrig ? 'Texas-Rig ' : ""}}
                                            {{$guiding->methods_topwater ? 'Topwater ' : ""}}
                                            {{$guiding->methods_vertikalangeln ? 'Vertikal-Angeln ' : ""}}
                                            {{$guiding->methods_wurm ? 'Wurm ' : ""}}
                                            {{$guiding->methods_sonstiges ? $guiding->methods_sonstiges : ""}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Ufer / Boot:</b>
                                            {{$guiding->fishing_from}}
                                        </p>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mt-5 pt-2 text-left">
                            <ul class="list-unstyled tour-details-two__overview-bottom-list" >
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Gewässer Typ:</b>
                                            {{$guiding->water_bach ? 'Bach ' : ""}}
                                            {{$guiding->water_baggersee ? 'Baggersee ' : ""}}
                                            {{$guiding->water_fluss ? 'Fluss ' : ""}}
                                            {{$guiding->water_hafen ? 'Hafen ' : ""}}
                                            {{$guiding->water_kanal ? 'Kanal ' : ""}}
                                            {{$guiding->water_meer ? 'Meer ' : ""}}
                                            {{$guiding->water_natursee ? 'Natursee ' : ""}}
                                            {{$guiding->water_stausee ? 'Stausee ' : ""}}
                                            {{$guiding->water_strom ? 'Strom ' : ""}}
                                            {{$guiding->water_talsperre ? 'Talsperre ' : ""}}
                                            {{$guiding->water_teich ? 'Teich ' : ""}}
                                            {{$guiding->water_sonstiges ? $guiding->water_sonstiges : ""}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Gast-/Gewässerkarte:</b>
                                            {{$guiding->required_special_license ? $guiding->required_special_license : 'Nein'}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Equipment:</b>
                                            {{$guiding->required_equipment == 'is_there' ? 'wird bereitgestellt' : $guiding->needed_equipment}}
                                        </p>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            </a>
        </div>
    </div>
</div>
