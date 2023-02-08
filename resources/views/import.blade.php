@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')

    <div class="card">
        <div class="card-body">
            <div class="woocommerce-importer">
                <h1 class="page-title">{{ trans('plugins/woocommerce-importer::woocommerce-importer.name') }}</h1>

                <form method="POST" action="{{ route('woocommerce-importer') }}"  enctype="multipart/form-data" class="import-woocommerce-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="alert alert-success success-message hidden"></div>

                                <div class="alert alert-warning error-message hidden"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6>{{ trans('plugins/woocommerce-importer::woocommerce-importer.woocommerce_store') }}</h6>
                            <div class="form-group">
                                <label for="site_url" class="control-label" data-toggle="tooltip" data-bs-toggle="tooltip"
                                       title="{{ trans('plugins/woocommerce-importer::woocommerce-importer.site_url') }}"
                                       data-placement="right">{{ trans('plugins/woocommerce-importer::woocommerce-importer.site_url') }}</label>
                                <input type="text" name="site_url" class="form-control" value="{{env('WOOCOMMERCE_STORE_URL')}}"  id="site_url">
                            </div>
                            <div class="form-group">
                                <label for="consumer_key" class="control-label" data-toggle="tooltip" data-bs-toggle="tooltip"
                                       title="{{ trans('plugins/woocommerce-importer::woocommerce-importer.consumer_key') }}"
                                       data-placement="right">{{ trans('plugins/woocommerce-importer::woocommerce-importer.consumer_key') }}</label>
                                <input type="text" name="consumer_key" class="form-control" value="{{env('WOOCOMMERCE_CONSUMER_KEY')}}"  id="consumer_key">
                            </div>
                            <div class="form-group">
                                <label for="consumer_sec" class="control-label" data-toggle="tooltip" data-bs-toggle="tooltip"
                                       title="{{ trans('plugins/woocommerce-importer::woocommerce-importer.consume_sec') }}"
                                       data-placement="right">{{ trans('plugins/woocommerce-importer::woocommerce-importer.consume_sec') }}</label>
                                <input type="text" name="consumer_sec" class="form-control" value="{{env('WOOCOMMERCE_CONSUMER_SECRET')}}" id="consumer_sec">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary import-woocommerce-data">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>{{ trans('plugins/woocommerce-importer::woocommerce-importer.checking') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
