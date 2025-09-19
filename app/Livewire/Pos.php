<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Pos extends Component
{
  use WithPagination;
  protected $paginationTheme = 'bootstrap';

  public $search = '';
  public $cart = [];
  public $client_id;
  public $discount = 0;
  public $total_paid;

  public $subtotal = 0;
  public $total = 0;

  public $categories = [];
  public $brands = [];

  public $selectedCategory = '';
  public $selectedBrand = '';

  public function mount()
  {
    $this->categories = Category::orderBy('name')->get();
    $this->brands = Brand::orderBy('name')->get();
  }

  public function render()
  {
    // $query = Product::query();
    $query = Product::whereIn('id', function ($subquery) {
      $subquery->select('product_id')
        ->from('store_products')
        ->whereIn('store_id', Auth::user()->stores->pluck('id')->toArray());
    });

    if ($this->search) {
      $query->where(function ($q) {
        $q->where('name', 'like', '%' . $this->search . '%')
          ->orWhere('reference', 'like', '%' . $this->search . '%');
      });
      // Limiter à 9 résultats max pour la recherche précise
      $products = $query->paginate(9);
    } else {
      if ($this->selectedCategory) {
        $query->where('category_id', $this->selectedCategory);
      }

      if ($this->selectedBrand) {
        $query->where('brand_id', $this->selectedBrand);
      }

      $products = $query->orderBy('name')->paginate(9);
    }

    $clients = Client::all();

    $this->calculateTotals();

    return view('livewire.pos', compact('products', 'clients'));
  }


  public function addItem($productId)
  {
    $product = Product::findOrFail($productId);

    if ($product->stores()->where('store_id', Auth::user()->stores()->first()->id)->first()->pivot->quantity <= 0) {
      notyf()->error('Product is out of stock.');
      return;
    }

    $index = $this->findItemInCart($productId);

    if ($index !== false) { // ✅ corrige ici
      $this->cart[$index]['quantity']++;
      $this->cart[$index]['subtotal'] = $this->cart[$index]['price'] * $this->cart[$index]['quantity'];
    } else {
      $this->cart[] = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => $product->sale_price,
        'quantity' => 1,
        'subtotal' => $product->sale_price,
      ];
    }

    notyf()->success('Produit ajouté au panier.');
  }

  public function updateQuantity($index, $quantity)
  {
    if ($quantity <= 0) {
      unset($this->cart[$index]);
      $this->cart = array_values($this->cart); // Re-index array
      return;
    }

    $product = Product::find($this->cart[$index]['id']);
    if ($quantity > $product->stores()->where('store_id', Auth::user()->stores()->first()->id)->first()->pivot->quantity) {
      $this->cart[$index]['quantity'] = $product->stores()->where('store_id', Auth::user()->stores()->first()->id)->first()->pivot->quantity;
      notyf()->error('Not enough stock for ' . $product->name);
    } else {
      $this->cart[$index]['quantity'] = $quantity;
    }

    $this->cart[$index]['subtotal'] = $this->cart[$index]['price'] * $this->cart[$index]['quantity'];
  }

  public function removeItem($index)
  {
    unset($this->cart[$index]);
    $this->cart = array_values($this->cart); // Re-index array
  }

  public function clearCart()
  {
    $this->cart = [];
    $this->discount = 0;
    $this->client_id = null;
    $this->total_paid = null;
  }

  public function saveSale()
  {
    $this->validate([
      'total_paid' => 'required|numeric|min:0',
      'client_id' => 'nullable|exists:clients,id',
    ]);

    if (empty($this->cart)) {
      notyf()->error('Cart is empty.');
      return;
    }

    DB::beginTransaction();
    try {
      $customer_id = $this->client_id ?? Client::first()->id;
      $sale = Sale::create([
        'client_id'   => $customer_id,
        'user_id'     => Auth::id(),
        'store_id'    => Auth::user()->stores()->first()->id, // ✅ on enregistre le magasin
        'total_amount' => $this->total,
        'total_paid'  => $this->total_paid,
        'sale_date'   => now(),
        'status'      => 'validated',
      ]);

      foreach ($this->cart as $item) {
        SaleItem::create([
          'sale_id' => $sale->id,
          'product_id' => $item['id'],
          'quantity' => $item['quantity'],
          'unit_price' => $item['price'],
          'total_price' => $item['subtotal'],
        ]);

        $store = Auth::user()->stores()->first();

        $product = Product::find($item['id']);

        $product->stores()->updateExistingPivot($store, [
          'quantity' => DB::raw("quantity - {$item['quantity']}")
        ]);
      }

      // Update client debt
      if ($this->client_id) {
        $client = Client::find($this->client_id);
        $debt = $this->total - $this->total_paid;
        if ($debt > 0) {
          $client->increment('debt', $debt);
        }
      }

      DB::commit();

      notyf()->success('Sale completed successfully.');
      $this->clearCart();
      $this->dispatch('facture-validee', url: route('invoice.print', ['sale' => $sale->id]));
    } catch (\Throwable $th) {
      DB::rollBack();
      dd($th);
      notyf()->error(__($th . 'Une erreur est survenue lors de la vente.'));
    }
  }

  private function findItemInCart($productId)
  {
    foreach ($this->cart as $index => $item) {
      if ($item['id'] == $productId) {
        return $index;
      }
    }
    return false;
  }

  private function calculateTotals()
  {
    $this->subtotal = array_reduce($this->cart, function ($carry, $item) {
      return $carry + $item['subtotal'];
    }, 0);

    $this->total = $this->subtotal - intval($this->discount);
  }
}
