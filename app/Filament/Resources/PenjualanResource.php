<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
use App\Models\Penjualan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Filament Components
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Get;

// Models
use App\Models\Pembeli;
use App\Models\Barang;
use App\Models\PenjualanBarang;

// DB
use Illuminate\Support\Facades\DB;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon  = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([

                    // =====================
                    // Step 1: Data Faktur
                    // =====================
                    Wizard\Step::make('Pesanan')
                        ->schema([
                            Forms\Components\Section::make('Faktur')
                                ->icon('heroicon-m-document-duplicate')
                                ->collapsible()
                                ->columns(3)
                                ->schema([
                                    TextInput::make('no_faktur')
                                        ->label('Nomor Faktur')
                                        ->default(fn () => Penjualan::getKodeFaktur())
                                        ->required()
                                        ->readonly(),

                                    DateTimePicker::make('tgl')
                                        ->label('Tanggal')
                                        ->default(now()),

                                    Select::make('pembeli_id')
                                        ->label('Pembeli')
                                        ->options(Pembeli::pluck('nama_pembeli', 'id')->toArray())
                                        ->placeholder('Pilih Pembeli')
                                        ->required(),

                                    TextInput::make('tagihan')
                                        ->default(0)
                                        ->hidden(),

                                    TextInput::make('status')
                                        ->default('pesan')
                                        ->hidden(),
                                ]),
                        ]),

                    // =====================
                    // Step 2: Pilih Barang
                    // =====================
                    Wizard\Step::make('Pilih Barang')
                        ->schema([
                            Repeater::make('items')
                                ->relationship('penjualanBarang')
                                ->schema([
                                    Select::make('barang_id')
                                        ->label('Barang')
                                        ->options(Barang::pluck('nama_barang', 'id')->toArray())
                                        ->required()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->reactive()
                                        ->placeholder('Pilih Barang')
                                        ->searchable()
                                        ->afterStateUpdated(function ($state, $set) {
                                            $barang = Barang::find($state);
                                            if (!$barang) return;

                                            // ✅ harga_barang sudah = harga jual, JANGAN dikali 1.2 lagi
                                            $set('harga_beli', $barang->harga_barang);
                                            $set('harga_jual', $barang->harga_barang);
                                        }),

                                    TextInput::make('harga_beli')
                                        ->label('Harga Beli')
                                        ->numeric()
                                        ->readonly()
                                        ->hidden()
                                        ->dehydrated(),

                                    TextInput::make('harga_jual')
                                        ->label('Harga Barang')
                                        ->numeric()
                                        ->readonly()
                                        ->dehydrated(),

                                    TextInput::make('jml')
                                        ->label('Jumlah')
                                        ->default(1)
                                        ->numeric()
                                        ->live()
                                        ->required(),

                                    DatePicker::make('tgl')
                                        ->label('Tanggal')
                                        ->default(today())
                                        ->required(),
                                ])
                                ->columns(['md' => 4])
                                ->addable()
                                ->deletable()
                                ->reorderable()
                                ->createItemButtonLabel('Tambah Item')
                                ->minItems(1)
                                ->required(),

                            // Tombol Proses
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('Proses')
                                    ->label('Proses')
                                    ->color('primary')
                                    ->action(function ($get) {
                                        $penjualan = Penjualan::updateOrCreate(
                                            ['no_faktur' => $get('no_faktur')],
                                            [
                                                'tgl'        => $get('tgl'),
                                                'pembeli_id' => $get('pembeli_id'),
                                                'status'     => 'pesan',
                                                'tagihan'    => 0,
                                            ]
                                        );

                                        foreach ($get('items') as $item) {
                                            PenjualanBarang::updateOrCreate(
                                                [
                                                    'penjualan_id' => $penjualan->id,
                                                    'barang_id'    => $item['barang_id'],
                                                ],
                                                [
                                                    'harga_beli' => $item['harga_beli'],
                                                    'harga_jual' => $item['harga_jual'],
                                                    'jml'        => $item['jml'],
                                                    'tgl'        => $item['tgl'],
                                                ]
                                            );

                                            // Kurangi stok barang
                                            $barang = Barang::find($item['barang_id']);
                                            if ($barang) {
                                                $barang->decrement('stok', $item['jml']);
                                            }
                                        }

                                        // Hitung dan update total tagihan
                                        $totalTagihan = PenjualanBarang::where('penjualan_id', $penjualan->id)
                                            ->sum(DB::raw('harga_jual * jml'));

                                        $penjualan->update(['tagihan' => $totalTagihan]);
                                    }),
                            ]),
                        ]),

                    // =====================
                    // Step 3: Pembayaran
                    // =====================
                    Wizard\Step::make('Pembayaran')
                        ->schema([
                            Placeholder::make('Tabel Pembayaran')
                                ->content(fn (Get $get) => view('filament.components.penjualan-table', [
                                    'pembayarans' => Penjualan::where('no_faktur', $get('no_faktur'))->get(),
                                ])),
                        ]),

                ])->columnSpan(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_faktur')
                    ->label('No Faktur')
                    ->searchable(),

                TextColumn::make('pembeli.nama_pembeli')
                    ->label('Nama Pembeli')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bayar' => 'success',
                        'pesan' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('tagihan')
                    ->label('Tagihan')
                    ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                    ->sortable()
                    ->alignment('end'),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pesan' => 'Pemesanan',
                        'bayar' => 'Pembayaran',
                    ])
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit'   => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }
}