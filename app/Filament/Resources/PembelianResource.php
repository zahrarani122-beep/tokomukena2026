<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Models\Pembelian;
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

// Models
use App\Models\Penjual;
use App\Models\Barang;
use App\Models\PembelianBarang;

// DB
use Illuminate\Support\Facades\DB;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pembelian';
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
                                ->columns(2)
                                ->schema([
                                    TextInput::make('no_faktur')
                                        ->label('Nomor Faktur')
                                        ->default(fn () => Pembelian::getKodeFaktur())
                                        ->required()
                                        ->readonly(),

                                    DateTimePicker::make('tgl')
                                        ->label('Tanggal')
                                        ->default(now()),

                                    Select::make('penjual_id')
                                        ->label('Penjual')
                                        ->options(Penjual::pluck('nama_penjual', 'id')->toArray())
                                        ->placeholder('Pilih Penjual')
                                        ->required(),

                                    TextInput::make('tagihan')
                                        ->default(0)
                                        ->hidden(),
                                ]),
                        ]),

                    // =====================
                    // Step 2: Pilih Barang
                    // =====================
                    Wizard\Step::make('Pilih Barang')
                        ->schema([
                            Repeater::make('items')
                                ->label('Daftar Barang')
                                ->schema([

                                    // === TOGGLE TIPE BARANG ===
                                    Select::make('is_barang_baru')
                                        ->label('Tipe Barang')
                                        ->options([
                                            '0' => 'Barang Lama',
                                            '1' => 'Barang Baru',
                                        ])
                                        ->default('0')
                                        ->live()
                                        ->reactive()
                                        ->columnSpan(['md' => 6]),

                                    // === BARANG LAMA ===
                                    Select::make('barang_id')
                                        ->label('Pilih Barang')
                                        ->options(Barang::pluck('nama_barang', 'id')->toArray())
                                        ->placeholder('Pilih Barang')
                                        ->required(fn ($get) => $get('is_barang_baru') === '0')
                                        ->visible(fn ($get) => $get('is_barang_baru') === '0')
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->live()
                                        ->reactive()
                                        ->searchable()
                                        ->afterStateUpdated(function ($state, $set) {
                                            $barang = Barang::find($state);
                                            if (!$barang) return;

                                            // Ambil harga beli terakhir dari pembelian sebelumnya
                                            // agar harga beli ke vendor tidak naik jadi harga jual
                                            $hargaBeliTerakhir = PembelianBarang::where('barang_id', $state)
                                                ->latest()
                                                ->value('harga_beli');

                                            $set('harga_beli', $hargaBeliTerakhir ?? $barang->harga_barang);
                                            $set('harga_jual', $barang->harga_barang); // harga_barang = harga jual ke customer
                                        })
                                        ->columnSpan(['md' => 6]),
                                        Placeholder::make('foto_preview')
                                        ->label('Foto Barang')
                                        ->visible(fn ($get) => $get('is_barang_baru') === '0' && !empty($get('foto_preview')))
                                        ->content(function ($get) {
                                        $foto = $get('foto_preview');
                                        if (!$foto) return 'Belum ada foto';

                                        // sesuaikan path foto dengan storage kamu
                                        $url = str_starts_with($foto, 'http') 
                                        ? $foto 
                                        : asset('storage/' . $foto);

                                        return new \Illuminate\Support\HtmlString(
                                        '<img src="' . $url . '" 
                                        style="height:120px; width:120px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;" 
                                        onerror="this.src=\'' . asset('storage/default.png') . '\'">'
                                        );
                                        })
                                        ->columnSpan(['md' => 6]),

                                    // === BARANG BARU ===
                                    TextInput::make('nama_barang_baru')
                                        ->label('Nama Barang Baru')
                                        ->placeholder('Masukkan nama barang baru')
                                        ->visible(fn ($get) => $get('is_barang_baru') === '1')
                                        ->required(fn ($get) => $get('is_barang_baru') === '1')
                                        ->dehydrated(true)
                                        ->columnSpan(['md' => 3]),

                                    TextInput::make('rating_baru')
                                        ->label('Rating (0-5)')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->maxValue(5)
                                        ->visible(fn ($get) => $get('is_barang_baru') === '1')
                                        ->dehydrated(true)
                                        ->columnSpan(['md' => 3]),

                                        Forms\Components\FileUpload::make('foto_baru')
                                        ->label('Foto Barang')
                                        ->image()
                                        ->directory('barang') // disimpan di storage/app/public/barang
                                        ->maxSize(2048)       // max 2MB
                                        ->imagePreviewHeight('100')
                                        ->visible(fn ($get) => $get('is_barang_baru') === '1')
                                        ->dehydrated(true)
                                        ->columnSpan(['md' => 6]),

                                    // === HARGA & JUMLAH ===
                                    TextInput::make('harga_beli')
                                        ->label('Harga Beli')
                                        ->numeric()
                                        ->live()
                                        ->afterStateUpdated(fn ($state, $set) =>
                                            $set('harga_jual', $state ? $state * 1.2 : 0)
                                        )
                                        ->dehydrated()
                                        ->columnSpan(['md' => 2]),

                                    TextInput::make('harga_jual')
                                        ->label('Harga Jual (+20%)')
                                        ->numeric()
                                        ->readonly()
                                        ->dehydrated()
                                        ->columnSpan(['md' => 2]),

                                    TextInput::make('jml')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->default(1)
                                        ->live()
                                        ->required()
                                        ->columnSpan(['md' => 1]),

                                    DatePicker::make('tgl')
                                        ->label('Tanggal')
                                        ->default(today())
                                        ->required()
                                        ->columnSpan(['md' => 1]),
                                ])
                                ->columns(['md' => 6])
                                ->addable()
                                ->deletable()
                                ->reorderable()
                                ->createItemButtonLabel('Tambah Item')
                                ->minItems(1)
                                ->required()
                                ->dehydrated(false), 
                        ]),

                    // ============================
                    // Step 3: Status Pembayaran
                    // ============================
                    Wizard\Step::make('Status Pembayaran')
                        ->schema([
                            Forms\Components\Section::make('Status Pembayaran')
                                ->icon('heroicon-m-banknotes')
                                ->columns(2)
                                ->schema([
                                    Select::make('status')
                                        ->label('Status Pembayaran')
                                        ->options([
                                            'lunas'  => 'Lunas',
                                            'hutang' => 'Hutang',
                                        ])
                                        ->default('lunas')
                                        ->required(),

                                    Placeholder::make('total_tagihan')
                                        ->label('Total Tagihan')
                                        ->content(function ($get) {
                                            $total = collect($get('items') ?? [])->sum(
                                                fn ($item) =>
                                                (float)($item['harga_beli'] ?? 0) * (int)($item['jml'] ?? 0)
                                            );
                                            return rupiah($total);
                                        }),
                                ]),
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

                TextColumn::make('penjual.nama_penjual')
                    ->label('Nama Penjual')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lunas'  => 'success',
                        'hutang' => 'danger',
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
                        'lunas'  => 'Lunas',
                        'hutang' => 'Hutang',
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
            'index'  => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit'   => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }
}