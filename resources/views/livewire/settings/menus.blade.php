<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span><i class="fas fa-arrow-left mr-3"></i>menus</span>
                        </a>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i
                                    class="fas fa-times"></i> Cancel</button>
                            @else
                            <button class="btn btn-primary btn-sm"
                                wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i>
                                Add New Menu</button>
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="dd">
                        <ol class="dd-list">
                            @foreach ($items as $item)
                            @if ($item->children && $item->children->count() > 0)
                            <li class="dd-item" data-id="{{$item->id}}">
                                <div class="dd-handle">{{$item->menu_label}}</div>
                                <ol class="dd-list">
                                    @foreach ($item->children()->orderBy('menu_order', 'ASC')->get() as $children)
                                    <li class="dd-item" data-id="{{$children->id}}">
                                        <div class="dd-handle">{{$children->menu_label}}</div>
                                    </li>
                                    @endforeach
                                </ol>
                            </li>
                            @else
                            <li class="dd-item" data-id="{{$item->id}}">
                                <div class="dd-handle">{{$item->menu_label}}</div>
                            </li>
                            @endif
                            @endforeach
                        </ol>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary pull-right"
                            wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal confirm --}}
        <div id="confirm-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog"
            aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="my-modal-title">Konfirmasi Hapus</h5>
                    </div>
                    <div class="modal-body">
                        <p>Apakah anda yakin hapus data ini.?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" wire:click='delete' class="btn btn-danger btn-sm"><i
                                class="fa fa-check pr-2"></i>Ya, Hapus</button>
                        <button class="btn btn-primary btn-sm" wire:click='_reset'><i
                                class="fa fa-times pr-2"></i>Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.css">
    @endpush
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>

    <script>
        document.addEventListener('livewire:load', function(e) {
        $('.dd').nestable({
            maxDepth:2,
            callback: function(l,e){
                // l is the main container
                // e is the element that was moved
                let menu = $('.dd').nestable('serialize')
                @this.call('changeMenu', getMenu(menu));
            }
        });
        window.livewire.on('loadForm', (data) => {
            
        });

        window.livewire.on('closeModal', (data) => {
            $('#confirm-modal').modal('hide')
        });
    })


    const getMenu = (menu) => {
        let final_menu = [];
        //initial variable
        let i = 1;
        //process each element
        $.each(menu, function(index, value){
            //local variable
            let item = {};
            //type of validation
            if(typeof(value.children) !== 'undefined'){
                let j = 1;
                item['id'] = value.id;
                item['order'] = i;
                item['children'] = [];
                //process each children
                $.each(value.children, function(index1, value1){
                    let child = {};
                    child['id'] = value1.id;
                    child['order'] = j;
                    item['children'].push(child);
                    j++;
                });
            }
            else{
                item['id'] = value.id;
                item['order'] = i;
                item['children'] = null;
            }
            //create the final menu
            final_menu.push(item);
            i++;
        });
        return final_menu
    }
    </script>
    @endpush
</div>