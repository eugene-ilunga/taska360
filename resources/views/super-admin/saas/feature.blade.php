@extends('super-admin.layouts.saas-app')

@section('header-section')
    @include('super-admin.saas.section.breadcrumb')
@endsection

@push('head-script')
    <style>
        .task360-features-highlight {
            position: relative;
            overflow: hidden;
            padding: 96px 0 72px;
            background:
                radial-gradient(circle at 8% 10%, rgba(255, 176, 32, 0.16) 0%, rgba(255, 176, 32, 0) 44%),
                radial-gradient(circle at 92% 84%, rgba(0, 196, 180, 0.16) 0%, rgba(0, 196, 180, 0) 46%),
                linear-gradient(180deg, #fffef8 0%, #ffffff 100%);
            border-bottom: 1px solid #f2f2f2;
        }

        .task360-features-highlight::before,
        .task360-features-highlight::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            z-index: 0;
        }

        .task360-features-highlight::before {
            width: 220px;
            height: 220px;
            top: -90px;
            right: -70px;
            background: rgba(255, 176, 32, 0.18);
            filter: blur(8px);
        }

        .task360-features-highlight::after {
            width: 260px;
            height: 260px;
            bottom: -130px;
            left: -70px;
            background: rgba(0, 196, 180, 0.14);
            filter: blur(10px);
        }

        .task360-features-wrap {
            position: relative;
            z-index: 1;
        }

        .task360-summary-box {
            padding: 26px 24px;
            border-radius: 9px;
            background: #ffffff;
            border: 1px solid #f0f0f0;
            box-shadow: 0 18px 40px rgba(32, 40, 68, 0.08);
            margin-bottom: 36px;
        }

        .task360-summary-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #8a6a00;
            background: #fff3d0;
            border: 1px solid #ffe2a3;
            border-radius: 999px;
            padding: 6px 12px;
            margin-bottom: 14px;
            letter-spacing: 0.2px;
        }

        .task360-summary-title {
            margin-bottom: 12px;
            font-size: 2rem;
            line-height: 1.25;
            color: #1f2a37;
            font-weight: 700;
        }

        .task360-summary-text {
            margin: 0;
            font-size: .95rem;
            line-height: 1.75;
            color: #4a5566;
        }

        .task360-feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .task360-feature-card {
            background: #ffffff;
            border: 1px solid #eceff4;
            border-radius: 9px;
            padding: 22px 20px;
            box-shadow: 0 12px 28px rgba(34, 48, 74, 0.07);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            height: 100%;
        }

        .task360-feature-card:hover {
            transform: translateY(-4px);
            border-color: #d8e2f1;
            box-shadow: 0 18px 36px rgba(34, 48, 74, 0.11);
        }

        .task360-feature-card h4 {
            margin-bottom: 9px;
            font-size: 1rem;
            line-height: 1.3;
            color: #1d2633;
            font-weight: 700;
        }

        .task360-feature-card p {
            margin: 0 0 12px;
            color: #4f5d70;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .task360-feature-list {
            list-style: none;
            margin: 0 0 12px;
            padding: 0;
        }

        .task360-feature-list li {
            position: relative;
            padding-left: 22px;
            margin-bottom: 5px;
            color: #2f3d50;
            font-size: 0.93rem;
            line-height: 1.55;
        }

        .task360-feature-list li::before {
            content: '\2022';
            position: absolute;
            left: 6px;
            top: 0;
            color: #0f9d8a;
            font-weight: 700;
        }

        .task360-feature-impact {
            margin: 0;
            padding: 9px 11px;
            border-radius: 10px;
            background: #f5fbff;
            border: 1px solid #d8edf8;
            color: #1f4966;
            font-size: 0.89rem;
            line-height: 1.55;
            font-weight: 600;
        }

        @media (max-width: 1199.98px) {
            .task360-feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .task360-features-highlight {
                padding: 76px 0 58px;
            }

            .task360-summary-title {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 767.98px) {
            .task360-feature-grid {
                grid-template-columns: 1fr;
            }

            .task360-summary-box {
                padding: 22px 18px;
                margin-bottom: 24px;
            }

            .task360-summary-title {
                font-size: 1.42rem;
            }

            .task360-summary-text {
                font-size: 0.98rem;
                line-height: 1.68;
            }
        }
    </style>
@endpush

@section('content')
    <section class="task360-features-highlight">
        <div class="container task360-features-wrap">
            <div class="task360-summary-box wow fadeInUp" data-wow-delay="0.1s">
                <span class="task360-summary-tag">Résumé ultra-impactant</span>
                <h2 class="task360-summary-title">TASK360 centralise vos projets, vos équipes, vos clients et vos finances dans une plateforme tout-en-un.</h2>
                <p class="task360-summary-text">Simplifiez la gestion quotidienne, accélérez l'exécution et gardez une vision claire de la performance de votre entreprise grâce à une expérience unifiée, rapide et intelligente.</p>
            </div>

            <div class="task360-feature-grid">
                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.1s">
                    <h4>1. Plateforme tout-en-un pour gérer votre entreprise</h4>
                    <p>Centralisez toutes vos opérations dans un seul outil.</p>
                    <ul class="task360-feature-list">
                        <li>Gestion des équipes, projets, clients et finances</li>
                        <li>Interface unique pour toute l'entreprise</li>
                        <li>Accès cloud depuis n'importe où</li>
                    </ul>
                    <p class="task360-feature-impact">Une solution complète qui remplace plusieurs outils à la fois</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.15s">
                    <h4>2. Gestion avancée des projets</h4>
                    <p>Pilotez tous vos projets avec précision.</p>
                    <ul class="task360-feature-list">
                        <li>Création de projets et suivi en temps réel</li>
                        <li>Gestion des tâches, délais et priorités</li>
                        <li>Visualisation Kanban et planning</li>
                    </ul>
                    <p class="task360-feature-impact">Gardez le contrôle total sur vos activités</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.2s">
                    <h4>3. Gestion intelligente des tâches</h4>
                    <p>Organisez efficacement le travail de vos équipes.</p>
                    <ul class="task360-feature-list">
                        <li>Attribution des tâches</li>
                        <li>Suivi de progression</li>
                        <li>Gestion des dépendances</li>
                    </ul>
                    <p class="task360-feature-impact">Une productivité maximale au quotidien</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.1s">
                    <h4>4. Gestion des équipes et RH</h4>
                    <p>Supervisez vos collaborateurs facilement.</p>
                    <ul class="task360-feature-list">
                        <li>Gestion des employés</li>
                        <li>Pointage (présence / absence)</li>
                        <li>Gestion des congés</li>
                    </ul>
                    <p class="task360-feature-impact">Une organisation interne fluide et structurée</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.15s">
                    <h4>5. CRM et gestion des clients</h4>
                    <p>Développez et fidélisez votre clientèle.</p>
                    <ul class="task360-feature-list">
                        <li>Gestion des leads et clients</li>
                        <li>Suivi des projets clients</li>
                        <li>Communication centralisée</li>
                    </ul>
                    <p class="task360-feature-impact">Ne perdez plus aucune opportunité</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.2s">
                    <h4>6. Facturation et gestion financière</h4>
                    <p>Automatisez vos revenus et votre comptabilité.</p>
                    <ul class="task360-feature-list">
                        <li>Création de factures et devis</li>
                        <li>Suivi des paiements</li>
                        <li>Gestion des revenus et dépenses</li>
                    </ul>
                    <p class="task360-feature-impact">Une gestion financière simplifiée et transparente</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.1s">
                    <h4>7. Communication et collaboration en temps réel</h4>
                    <p>Travaillez efficacement en équipe.</p>
                    <ul class="task360-feature-list">
                        <li>Messagerie interne</li>
                        <li>Notifications instantanées</li>
                        <li>Partage d'informations</li>
                    </ul>
                    <p class="task360-feature-impact">Une collaboration rapide et efficace</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.15s">
                    <h4>8. Système de tickets et support</h4>
                    <p>Gérez facilement les demandes et incidents.</p>
                    <ul class="task360-feature-list">
                        <li>Création de tickets</li>
                        <li>Suivi et résolution</li>
                        <li>Support client structuré</li>
                    </ul>
                    <p class="task360-feature-impact">Améliorez la satisfaction client</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.2s">
                    <h4>9. Rapports et tableaux de bord</h4>
                    <p>Prenez des décisions basées sur des données fiables.</p>
                    <ul class="task360-feature-list">
                        <li>Rapports détaillés (projets, finances, RH)</li>
                        <li>Analyse des performances</li>
                        <li>Indicateurs clés</li>
                    </ul>
                    <p class="task360-feature-impact">Une vision claire pour piloter votre croissance</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.1s">
                    <h4>10. Gestion des rôles et sécurité</h4>
                    <p>Contrôlez l'accès et sécurisez vos données.</p>
                    <ul class="task360-feature-list">
                        <li>Rôles personnalisés</li>
                        <li>Permissions avancées</li>
                        <li>Accès sécurisé</li>
                    </ul>
                    <p class="task360-feature-impact">Une plateforme fiable et professionnelle</p>
                </article>

                <article class="task360-feature-card wow fadeInUp" data-wow-delay="0.15s">
                    <h4>11. Accessible sur tous les appareils</h4>
                    <p>Travaillez où que vous soyez.</p>
                    <ul class="task360-feature-list">
                        <li>Compatible mobile, tablette et desktop</li>
                        <li>Interface responsive</li>
                        <li>Synchronisation en temps réel</li>
                    </ul>
                    <p class="task360-feature-impact">Votre bureau partout avec vous</p>
                </article>
            </div>
        </div>
    </section>

    @forelse($frontFeatures as $frontFeature)
        <!-- START Saas Features -->
        <section class="border-bottom bg-white sp-100 pb-3 overflow-hidden">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="sec-title mb-60">
                            <h3>{{ $frontFeature->title }}</h3>
                            <p>{!!  $frontFeature->description !!}</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @forelse($frontFeature->features as $feature)
                        <div class="col-md-4 col-sm-6 col-12 mb-60">
                            @if($feature->type != 'image')
                                <div class="saas-f-box">
                                    <div class="align-items-center icon justify-content-center">
                                        <i class="{{ $feature->icon }}"></i>
                                    </div>
                                    <h5>{{ $feature->title }}</h5>
                                    <p class="mb-0">{!!  $feature->description !!} </p>
                                </div>
                            @else
                                <div class="integrate-box shadow">
                                    <img src="{{ $feature->image_url }}" alt="{{ $feature->title }}">
                                    <h5 class="mb-0">{{ $feature->title }} </h5>
                                </div>
                            @endif

                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </section>
    @empty
    @endforelse
    {{--<!-- END SAAS Features -->--}}

    <!-- START Clients Section -->
    @include('super-admin.saas.section.client')
    <!-- END Clients Section -->

    <!-- START Integration Section -->
    <section class="sp-100-70 bg-white">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="sec-title mb-60">
                        <h3>{{ $trFrontDetail->favourite_apps_title }}</h3>
                        <p>{{ $trFrontDetail->favourite_apps_detail }}</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                @forelse($featureApps as $index => $featureApp)
                    <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-30 wow fadeIn" data-wow-delay="0.4s">
                        <div class="integrate-box shadow">
                            <img style="height: 55px" src="{{ $featureApp->image_url }}" alt="{{ $featureApp->title }}">
                            <h5 class="mb-0">{{ $featureApp->title }} </h5>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </section>
    <!-- END Integration Section -->
@endsection
@push('footer-script')

@endpush
