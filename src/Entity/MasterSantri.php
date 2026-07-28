<?php

declare(strict_types=1);

namespace App\Entity;

use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * @property int $kds
 * @property int $stambuk
 * @property string $nama
 * @property string $kelas
 * @property string $rayon
 * @property string $negara
 * @property string $kewarganegaraan
 * @property string $tempat_lahir
 * @property string $tanggal_lahir
 * @property string $nama_ibu
 * @property string $nama_ayah
 * @property int $no_ibu
 * @property int $no_ayah
 * @property int $no_hp_alternatif
 * @property string $alamat
 * @property string $pondok
 * @property string $jenis_kelamin
 * @property string $path_foto
 * @property int $aktif
 * @property string $kode
 */
final class MasterSantri extends ActiveRecord
{
    public function tableName(): string
    {
        return 'master_santri';
    }

    public function getPaspors()
    {
        return $this->hasMany(MtbPaspor::class, ['kds' => 'kds']);
    }

    public function getItas()
    {
        return $this->hasMany(MtbItas::class, ['kds' => 'kds']);
    }
}
