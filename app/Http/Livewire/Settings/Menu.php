<?php

namespace App\Http\Livewire\Settings;

use App\Models\Menu as ModelsMenu;
use Livewire\Component;


class Menu extends Component
{

    public $menus_id;
    public $menu_label;
    public $menu_route;
    public $menu_icon;
    public $menu_order;
    public $parent_id;
    public $menus = [];




    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataById', 'getId'];

    public function render()
    {
        return view('livewire.settings.menus', [
            'items' => ModelsMenu::whereNull('parent_id')->orderBy('menu_order', 'ASC')->get()
        ]);
    }

    public function changeMenu($datas)
    {
        foreach ($datas as $data) {
            if ($data['children']) {
                ModelsMenu::find($data['id'])->update([
                    'menu_order' => $data['order'],
                    'parent_id' => null,
                ]);
                foreach ($data['children'] as $children) {
                    ModelsMenu::find($children['id'])->update([
                        'parent_id' => $data['id'],
                        'menu_order' => $children['order'],
                    ]);
                }
            } else {
                ModelsMenu::find($data['id'])->update([
                    'menu_order' => $data['order'],
                    'parent_id' => null,
                ]);
            }
        }

        return $this->emit('showAlert', ['msg' => 'Menu Berhasil Diupdate']);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'menu_label'  => $this->menu_label,
            'menu_route'  => $this->menu_route,
            'menu_icon'  => $this->menu_icon,
            'menu_order'  => $this->menu_order,
            'parent_id'  => $this->parent_id
        ];

        ModelsMenu::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'menu_label'  => $this->menu_label,
            'menu_route'  => $this->menu_route,
            'menu_icon'  => $this->menu_icon,
            'menu_order'  => $this->menu_order,
            'parent_id'  => $this->parent_id
        ];
        $row = ModelsMenu::find($this->menus_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        ModelsMenu::find($this->menus_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'menu_label'  => 'required',
            'menu_route'  => 'required',
            'menu_icon'  => 'required',
            'menu_order'  => 'required',
            'parent_id'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataById($menus_id)
    {
        $menus = ModelsMenu::find($menus_id);
        $this->menus_id = $menus->id;
        $this->menu_label = $menus->menu_label;
        $this->menu_route = $menus->menu_route;
        $this->menu_icon = $menus->menu_icon;
        $this->menu_order = $menus->menu_order;
        $this->parent_id = $menus->parent_id;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getId($menus_id)
    {
        $menus = ModelsMenu::find($menus_id);
        $this->menus_id = $menus->id;
    }

    public function toggleForm($form)
    {
        $this->form_active = $form;
        $this->emit('loadForm');
    }

    public function showModal()
    {
        $this->emit('showModal');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->menus_id = null;
        $this->menu_label = null;
        $this->menu_route = null;
        $this->menu_icon = null;
        $this->menu_order = null;
        $this->parent_id = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
