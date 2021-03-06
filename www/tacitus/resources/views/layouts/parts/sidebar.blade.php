<div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse">
        <ul class="nav" id="side-menu">
            <li>
                <a href="{{ url('/') }}"><i class="fa fa-dashboard fa-fw"></i> Home</a>
            </li>
            <li>
                <a href="{{ url('/tutorial') }}"><i class="fa fa-question-circle fa-fw"></i> Tutorial</a>
            </li>
            @if(user_can(\App\Utils\Permissions::VIEW_DATASETS))
                <li>
                    <a href="{{ route('datasets-lists') }}"><i class="fa fa-database fa-fw"></i> Datasets</a>
                </li>
            @endif
            @if(user_can(\App\Utils\Permissions::VIEW_SELECTIONS))
                <li>
                    <a href="{{ route('selections-lists') }}"><i class="fa fa-table fa-fw"></i> Selections</a>
                </li>
            @endif
            @if(user_can(\App\Utils\Permissions::USE_TOOLS))
                <li>
                    <a href="#"><i class="fa fa-wrench fa-fw"></i> Tools<span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
                        <li>
                            <a href="{{ route('platforms-lists') }}"><i class="fa fa-map-signs fa-fw"></i>
                                Platforms
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('mapped-selections-lists') }}">
                                <i class="fa fa-map fa-fw"></i>
                                Mapped Selections
                            </a>
                        </li>
                        @if(user_can(\App\Utils\Permissions::INTEGRATE_DATASETS))
                            <li>
                                <a href="{{ route('integrations-lists') }}">
                                    <i class="fa fa-link fa-fw"></i> Integrator
                                </a>
                            </li>
                        @endif
                    </ul>
                    <!-- /.nav-second-level -->
                </li>
            @endif
            @if(user_can(\App\Utils\Permissions::VIEW_JOBS))
                <li>
                    <a href="{{ route('jobs-list') }}"><i class="fa fa-cog fa-fw"></i> Jobs</a>
                </li>
            @endif
            @if(user_can(\App\Utils\Permissions::ADMINISTER))
                <li>
                    <a href="{{ route('tasks-list') }}"><i class="fa fa-tasks fa-fw"></i> Tasks</a>
                </li>
                <li>
                    <a href="{{ route('user::list') }}"><i class="fa fa-users fa-fw"></i> Users</a>
                </li>
            @endif
            {{--
            <li>
                <a href="#"><i class="fa fa-bar-chart-o fa-fw"></i> Charts<span class="fa arrow"></span></a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="flot.html">Flot Charts</a>
                    </li>
                    <li>
                        <a href="morris.html">Morris.js Charts</a>
                    </li>
                </ul>
                <!-- /.nav-second-level -->
            </li>
            <li>
                <a href="tables.html"><i class="fa fa-table fa-fw"></i> Tables</a>
            </li>
            <li>
                <a href="forms.html"><i class="fa fa-edit fa-fw"></i> Forms</a>
            </li>
            <li>
                <a href="#"><i class="fa fa-wrench fa-fw"></i> UI Elements<span class="fa arrow"></span></a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="panels-wells.html">Panels and Wells</a>
                    </li>
                    <li>
                        <a href="buttons.html">Buttons</a>
                    </li>
                    <li>
                        <a href="notifications.html">Notifications</a>
                    </li>
                    <li>
                        <a href="typography.html">Typography</a>
                    </li>
                    <li>
                        <a href="icons.html"> Icons</a>
                    </li>
                    <li>
                        <a href="grid.html">Grid</a>
                    </li>
                </ul>
                <!-- /.nav-second-level -->
            </li>
            <li>
                <a href="#"><i class="fa fa-sitemap fa-fw"></i> Multi-Level Dropdown<span
                            class="fa arrow"></span></a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="#">Second Level Item</a>
                    </li>
                    <li>
                        <a href="#">Second Level Item</a>
                    </li>
                    <li>
                        <a href="#">Third Level <span class="fa arrow"></span></a>
                        <ul class="nav nav-third-level">
                            <li>
                                <a href="#">Third Level Item</a>
                            </li>
                            <li>
                                <a href="#">Third Level Item</a>
                            </li>
                            <li>
                                <a href="#">Third Level Item</a>
                            </li>
                            <li>
                                <a href="#">Third Level Item</a>
                            </li>
                        </ul>
                        <!-- /.nav-third-level -->
                    </li>
                </ul>
                <!-- /.nav-second-level -->
            </li>
            <li>
                <a href="#"><i class="fa fa-files-o fa-fw"></i> Sample Pages<span class="fa arrow"></span></a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="blank.html">Blank Page</a>
                    </li>
                    <li>
                        <a href="login.html">Login Page</a>
                    </li>
                </ul>
                <!-- /.nav-second-level -->
            </li>
            --}}
        </ul>
    </div>
    <!-- /.sidebar-collapse -->
</div>
<!-- /.navbar-static-side -->