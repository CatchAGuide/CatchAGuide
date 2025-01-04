<div class="modal fade" id="vacationModal{{$vacation->id}}" tabindex="-1" role="dialog" aria-labelledby="vacationModal{{$vacation->id}}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" style="padding-right: 40px; padding-bottom: 20px"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <a href="{{ route('vacations.show', [$vacation->id,$vacation->slug]) }}" >
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img class=""
                             src="{{asset('images/' . $vacation->thumbnail_path)}}"
                             alt="" style="width: 100%; height: 500px; object-fit: cover;">

                    </div>
                    <div class="col-md-12">
                        <h3 class="tour-details-two__title">{{$vacation->title}}</h3>
                        <ul class="list-unstyled tour-details__top-list">
                            <li>
                                <div class="icon">
                                    <span class="icon-clock"></span>
                                </div>
                                <div class="text">
                                    <p>Dauer</p>
                                    {{-- {{ two($vacation->duration) }} --}}
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <span class="icon-user"></span>
                                </div>
                                <div class="text">
                                    <p>Max. Gäste</p>
                                    {{-- {{$vacation->max_guests}} --}}
                                </div>
                            </li>
                            <li>
                                <div class="icon">
                                    <span class="icon-place"></span>
                                </div>
                                <div class="text">
                                    <p>Ort</p>
                                    {{$vacation->location}}
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
                                            {{-- {{$vacation->target_fish_aal ? 'AAl ' : ""}}
                                            {{$vacation->target_fish_aland ? 'Aland ' : ""}}
                                            {{$vacation->target_fish_aesche ? 'Äsche ' : ""}}
                                            {{$vacation->target_fish_barbe ? 'Barbe ' : ""}}
                                            {{$vacation->target_fish_barsch ? 'Barsch ' : ""}}
                                            {{$vacation->target_fish_brasse ? 'Brasse ' : ""}}
                                            {{$vacation->target_fish_doebel ? 'Döbel ' : ""}}
                                            {{$vacation->target_fish_forelle ? 'Forelle ' : ""}}
                                            {{$vacation->target_fish_hecht ? 'Hecht ' : ""}}
                                            {{$vacation->target_fish_huchen ? 'Huchen ' : ""}}
                                            {{$vacation->target_fish_karpfen ? 'Karpfen ' : ""}}
                                            {{$vacation->target_fish_nase ? 'Nase ' : ""}}
                                            {{$vacation->target_fish_rapfen ? 'Rapfen ' : ""}}
                                            {{$vacation->target_fish_rotauge ? 'Rotauge ' : ""}}
                                            {{$vacation->target_fish_rotfeder ? 'Rotfeder ' : ""}}
                                            {{$vacation->target_fish_schleie ? 'Schleie ' : ""}}
                                            {{$vacation->target_fish_wels ? 'Wels ' : ""}}
                                            {{$vacation->target_fish_zander ? 'Zander ' : ""}}
                                            {{$vacation->target_fish_sonstiges ? $vacation->target_fish_sonstiges : ""}} --}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Angel Art:</b>
                                            {{-- {{$vacation->fishing_type}} --}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Technik:</b>
                                            {{-- {{$vacation->methods_carolinarig ? 'Carolina-Rig ' : ""}}
                                            {{$vacation->methods_dropshot ? 'Dropshot ' : ""}}
                                            {{$vacation->methods_eisfischen ? 'Eisfischen ' : ""}}
                                            {{$vacation->methods_feederangeln ? 'Feederangeln ' : ""}}
                                            {{$vacation->methods_fliegenfischen ? 'Fliegenfischen ' : ""}}
                                            {{$vacation->methods_grundblei ? 'Grundblei ' : ""}}
                                            {{$vacation->methods_hardbait ? 'Hardbait ' : ""}}
                                            {{$vacation->methods_jerkbaitangeln ? 'Jerkbaitangeln ' : ""}}
                                            {{$vacation->methods_jiggen ? 'Jiggen ' : ""}}
                                            {{$vacation->methods_koederfisch ? 'Köderfisch ' : ""}}
                                            {{$vacation->methods_pose ? 'Pose ' : ""}}
                                            {{$vacation->methods_schleppangeln ? 'Schleppangeln ' : ""}}
                                            {{$vacation->methods_texasrig ? 'Texas-Rig ' : ""}}
                                            {{$vacation->methods_topwater ? 'Topwater ' : ""}}
                                            {{$vacation->methods_vertikalangeln ? 'Vertikal-Angeln ' : ""}}
                                            {{$vacation->methods_wurm ? 'Wurm ' : ""}}
                                            {{$vacation->methods_sonstiges ? $vacation->methods_sonstiges : ""}} --}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Ufer / Boot:</b>
                                            {{-- {{$vacation->fishing_from}} --}}
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
                                            {{-- {{$vacation->water_bach ? 'Bach ' : ""}}
                                            {{$vacation->water_baggersee ? 'Baggersee ' : ""}}
                                            {{$vacation->water_fluss ? 'Fluss ' : ""}}
                                            {{$vacation->water_hafen ? 'Hafen ' : ""}}
                                            {{$vacation->water_kanal ? 'Kanal ' : ""}}
                                            {{$vacation->water_meer ? 'Meer ' : ""}}
                                            {{$vacation->water_natursee ? 'Natursee ' : ""}}
                                            {{$vacation->water_stausee ? 'Stausee ' : ""}}
                                            {{$vacation->water_strom ? 'Strom ' : ""}}
                                            {{$vacation->water_talsperre ? 'Talsperre ' : ""}}
                                            {{$vacation->water_teich ? 'Teich ' : ""}}
                                            {{$vacation->water_sonstiges ? $vacation->water_sonstiges : ""}} --}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Gast-/Gewässerkarte:</b>
                                            {{-- {{$vacation->required_special_license ? $vacation->required_special_license : 'Nein'}} --}}
                                        </p>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <div class="text">
                                        <p><b>Equipment:</b>
                                            {{-- {{$vacation->required_equipment == 'is_there' ? 'wird bereitgestellt' : $vacation->needed_equipment}} --}}
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
