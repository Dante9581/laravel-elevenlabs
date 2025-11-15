# Laravel ElevenLabs

[![Latest Version](https://img.shields.io/packagist/v/digitalcorehub/laravel-elevenlabs.svg?style=flat-square)](https://packagist.org/packages/digitalcorehub/laravel-elevenlabs)
[![Total Downloads](https://img.shields.io/packagist/dt/digitalcorehub/laravel-elevenlabs.svg?style=flat-square)](https://packagist.org/packages/digitalcorehub/laravel-elevenlabs)
[![License](https://img.shields.io/packagist/l/digitalcorehub/laravel-elevenlabs.svg?style=flat-square)](https://packagist.org/packages/digitalcorehub/laravel-elevenlabs)

ElevenLabs Text-to-Speech (TTS), Speech-to-Text (STT), Voice Management ve Dubbing API'lerini Laravel 12 uygulamalarında modern, akıcı ve sezgisel bir arayüzle kullanmanızı sağlayan bir paket.

**📖 [English Documentation](README.md) | [Türkçe Dokümantasyon](README.tr.md)**

## 📋 Gereksinimler

- PHP 8.2 veya üzeri
- Laravel 12.0 veya üzeri

## 🚀 Kurulum

Paketi Composer ile kurabilirsiniz:

```bash
composer require digitalcorehub/laravel-elevenlabs
```

## ⚙️ Yapılandırma

Yapılandırma dosyasını yayınlayın:

```bash
php artisan vendor:publish --tag=elevenlabs-config
```

Bu komut `config/elevenlabs.php` dosyasını oluşturur.

### Ortam Değişkenleri

`.env` dosyanıza aşağıdakileri ekleyin:

```env
ELEVENLABS_API_KEY=your_api_key_here
ELEVENLABS_DEFAULT_VOICE=nova
ELEVENLABS_DEFAULT_FORMAT=mp3_44100_128
ELEVENLABS_BASE_URL=https://api.elevenlabs.io/v1
ELEVENLABS_TIMEOUT=30
```

### Yapılandırma Seçenekleri

- **api_key**: ElevenLabs API anahtarınız (gerekli)
- **base_url**: ElevenLabs API'nin base URL'i (varsayılan: `https://api.elevenlabs.io/v1`)
- **default_voice**: Varsayılan ses ID'si (varsayılan: `nova`)
- **default_format**: Varsayılan ses formatı (varsayılan: `mp3_44100_128`)
- **timeout**: İstek zaman aşımı saniye cinsinden (varsayılan: `30`)

## 📖 Kullanım

## Text-to-Speech (TTS)

### Temel TTS Kullanımı

Paket, metinden sese dönüştürme için akıcı bir API sağlar:

```php
use DigitalCoreHub\LaravelElevenLabs\Facades\ElevenLabs;

// Ses oluştur ve depolamaya kaydet
ElevenLabs::tts()
    ->voice('nova')
    ->text('Laravel\'den merhaba')
    ->format('mp3_44100_128')
    ->save('voices/merhaba.mp3');
```

### Varsayılanları Kullanma

Varsayılan ses ve format yapılandırdıysanız, bunları atlayabilirsiniz:

```php
ElevenLabs::tts()
    ->text('Laravel\'den merhaba')
    ->save('voices/merhaba.mp3');
```

### Ses Dosyası Nesnesi Alma

Doğrudan kaydetmek yerine, bir `AudioFile` nesnesi alabilirsiniz:

```php
$audioFile = ElevenLabs::tts()
    ->voice('nova')
    ->text('Laravel\'den merhaba')
    ->format('mp3_44100_128')
    ->generate();

// İçeriğe eriş
$content = $audioFile->getContent();

// Formatı al
$format = $audioFile->getFormat();

// Farklı bir konuma kaydet
$audioFile->save('custom/path/audio.mp3', 's3');
```

### Özel Ses Ayarları

Ses ayarlarını özelleştirebilirsiniz (stability, similarity_boost, vb.):

```php
ElevenLabs::tts()
    ->voice('nova')
    ->text('Laravel\'den merhaba')
    ->voiceSettings([
        'stability' => 0.7,
        'similarity_boost' => 0.8,
    ])
    ->save('voices/merhaba.mp3');
```

## Speech-to-Text (STT)

### Temel STT Kullanımı

Paket, ses dosyalarını metne dönüştürmek için akıcı bir API sağlar:

```php
// Ses dosyasını metne dönüştür
$result = ElevenLabs::stt()
    ->file('audio.wav')
    ->transcribe();

// Dönüştürülen metne eriş
echo $result->text;

// Kelimeler dizisine eriş (varsa)
$words = $result->words;

// Güven skoruna eriş (varsa)
$confidence = $result->confidence;
```

### Depolama Disklerini Kullanma

Herhangi bir Laravel depolama diskinden dosya dönüştürebilirsiniz:

```php
// Local depolamadan
$result = ElevenLabs::stt()
    ->file('audio/kayit.wav', 'local')
    ->transcribe();

// S3'ten
$result = ElevenLabs::stt()
    ->file('audio/kayit.wav', 's3')
    ->transcribe();
```

## Voice Management (Ses Yönetimi)

### Tüm Sesleri Listeleme

Mevcut tüm seslerin koleksiyonunu alın:

```php
$voices = ElevenLabs::voices()->list();

// Sesler arasında döngü
foreach ($voices as $voice) {
    echo $voice->name;
    echo $voice->voiceId;
}

// ID'ye göre ses bul
$voice = $voices->findById('voice-id');

// İsme göre sesler bul
$found = $voices->findByName('Nova');
```

### Özel Ses Oluşturma

Ses dosyalarını kullanarak özel bir ses oluşturun:

```php
// Mutlak dosya yolları kullanarak
$voice = ElevenLabs::voices()
    ->name('Özel Sesim')
    ->files(['/path/to/voice1.wav', '/path/to/voice2.wav'])
    ->description('Projem için özel bir ses')
    ->labels(['accent' => 'british', 'age' => 'young'])
    ->create();

// Depolama disk dosyalarını kullanarak
$voice = ElevenLabs::voices()
    ->name('Özel Sesim')
    ->files([
        ['path' => 'voices/voice1.wav', 'disk' => 'local'],
        ['path' => 'voices/voice2.wav', 'disk' => 's3'],
    ])
    ->create();
```

## Dubbing (Otomatik Dublaj Motoru)

### Temel Dublaj Kullanımı

Videoları veya ses dosyalarını farklı dillere dublaj edin:

```php
// Dublajı senkron olarak çalıştır
$result = ElevenLabs::dubbing()
    ->source('input.mp4')
    ->target('tr')
    ->run();

// Durumu kontrol et
echo $result->status; // processing, completed, failed
echo $result->jobId;
echo $result->outputUrl; // Tamamlandığında mevcut
```

### Kuyruk ile Arka Plan Dublajı

Uzun süren dublaj işleri için kuyruğu kullanın:

```php
// Kuyruğa gönder
ElevenLabs::dubbing()
    ->source('input.mp4')
    ->target('tr')
    ->dispatch();

// Veya parametrelerle
ElevenLabs::dubbing()->dispatch('input.mp4', 'tr');
```

### Dublaj Durumunu Kontrol Etme

Bir dublaj işinin durumunu kontrol edin:

```php
$result = ElevenLabs::dubbing()->status('job-id');

if ($result->isCompleted()) {
    // Dublajlı dosyayı indir
    $outputUrl = $result->outputUrl;
}

if ($result->isInProgress()) {
    // İş hala işleniyor
}

if ($result->isFailed()) {
    // İş başarısız oldu
}
```

## 🔄 Kuyruk Kullanımı

TTS oluşturma işlerini kolayca kuyruğa alabilirsiniz:

```php
use Illuminate\Support\Facades\Queue;

Queue::push(function () {
    ElevenLabs::tts()
        ->text('Bu arka planda işlenecek')
        ->save('voices/queued.mp3');
});
```

## 🧪 Test Etme

Paket test amaçlı fake provider'lar içerir:

```php
use DigitalCoreHub\LaravelElevenLabs\Tests\Fake\FakeTtsProvider;
use DigitalCoreHub\LaravelElevenLabs\Http\Endpoints\TtsEndpoint;
use DigitalCoreHub\LaravelElevenLabs\Http\Clients\ElevenLabsClient;

// Test kurulumunuzda
$this->app->singleton(TtsEndpoint::class, function ($app) {
    $client = $app->make(ElevenLabsClient::class);
    return new FakeTtsProvider($client);
});
```

## 🛣️ Yol Haritası

### v0.1 - Text-to-Speech (TTS) ✅
- [x] TTS oluşturma için akıcı API
- [x] Çoklu ses formatı desteği
- [x] Özel ses ayarları
- [x] Depolama entegrasyonu
- [x] Yapılandırma yönetimi
- [x] Kapsamlı test kapsamı

### v0.2 - Speech-to-Text (STT) ✅
- [x] STT dönüştürme için akıcı API
- [x] Dosya yükleme desteği (local ve depolama diskleri)
- [x] TranscriptionResult veri modeli
- [x] Çoklu ses formatı desteği
- [x] Özel model seçimi
- [x] Kelimeler dizisi ve güven skorları
- [x] Kapsamlı test kapsamı

### v0.3 - Voice Management (Ses Yönetimi) ✅
- [x] Ses yönetimi için akıcı API
- [x] Listeleme ve getirme işlemleri
- [x] Dosya yükleme ile özel ses oluşturma
- [x] Ses silme desteği
- [x] Ses senkronizasyonu işlevselliği
- [x] SyncVoicesJob ile kuyruk desteği
- [x] Event sistemi (VoiceCreated, VoiceSynced)
- [x] Voice ve VoiceCollection veri modelleri
- [x] Depolama disk entegrasyonu
- [x] Kapsamlı test kapsamı

### v0.4 - Dubbing (Otomatik Dublaj Motoru) ✅
- [x] Video/ses dublajı için akıcı API
- [x] Kaynak dosya desteği (local ve depolama diskleri)
- [x] Hedef dil seçimi
- [x] Dublaj seçenekleri desteği
- [x] İş durumu kontrolü
- [x] RunDubbingJob ile kuyruk desteği
- [x] Event sistemi (DubbingCompleted)
- [x] DubbingResult veri modeli
- [x] Durum kontrol metodları (isCompleted, isInProgress, isFailed)
- [x] Depolama disk entegrasyonu
- [x] Kapsamlı test kapsamı

## 📝 Lisans

Bu paket [MIT lisansı](LICENSE) altında açık kaynaklı bir yazılımdır.

## 🤝 Katkıda Bulunma

Katkılarınızı bekliyoruz! Lütfen bir Pull Request göndermekten çekinmeyin.

## 📧 Destek

Sorunlar, sorular veya katkılar için lütfen GitHub'da bir issue açın.

---

[DigitalCoreHub](https://digitalcorehub.com) tarafından ❤️ ile yapılmıştır

