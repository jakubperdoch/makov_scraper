<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Brosland\Extractor\Galileo\Domain\FileDto;
use Brosland\Extractor\Galileo\Domain\PostDto;
use DateTimeImmutable;

function sanitizeFilename($filename)
{
  $normalizeChars = array(
    'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
    'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
    'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
    'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
    'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
    'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
    'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'b',
    'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r', 'ě' => 'e', 'ň' => 'n', 'ů' => 'u', 'ĺ' => 'l', 'ŕ' => 'r', 'ť' => 't', 'ä' => 'a',
    'ô' => 'o', 'Ľ' => 'L', 'ľ' => 'l', 'Ť' => 'T', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'ď' => 'd', 'Ě' => 'E', 'ň' => 'n',
  );
  $filename = strtr($filename, $normalizeChars);
  $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
  $filename = preg_replace('/_+/', '_', $filename);
  return $filename;
}

function parseDate($dateString)
{
  return DateTimeImmutable::createFromFormat('j. n. Y H:i', $dateString) ?: new DateTimeImmutable();
}

$baseUrl = 'https://www.makov.sk/';
$relativePath = 'obec-2/centrum-socialnych-sluzieb/zivot-v-zps/';
$maxPages = 2;

$client = new Client();
$outputDir = './scraped_data';
$imagesDir = $outputDir . '/images';
$pdfDir = $outputDir . '/pdf';

$filesystemAdapter = new LocalFilesystemAdapter(__DIR__);
$filesystem = new Filesystem($filesystemAdapter);

$filesystem->createDirectory($outputDir);
$filesystem->createDirectory($imagesDir);
$filesystem->createDirectory($pdfDir);

$data = [];

for ($pageNum = 1; $pageNum <= $maxPages; $pageNum++) {
  $url = $baseUrl . $relativePath . '?page=' . $pageNum;
  $response = $client->get($url);
  $html = $response->getBody()->getContents();

  $crawler = new Crawler($html);

  $posts = $crawler->filter('.event-link')->each(function (Crawler $node) use ($baseUrl) {
    $relativeUrl = $node->attr('href');
    $absoluteUrl = strpos($relativeUrl, 'http') === 0 ? $relativeUrl : $baseUrl . ltrim($relativeUrl, '/');
    return [
      'title' => trim($node->text()),
      'url' => $absoluteUrl
    ];
  });

  foreach ($posts as $post) {
    try {
      $response = $client->get($post['url']);
      $html = $response->getBody()->getContents();
      $crawler = new Crawler($html);

      $title = $crawler->filter('.gcm-main h1')->count() ? $crawler->filter('.gcm-main h1')->text() : 'No title found';
      $dateUploaded = $crawler->filter('.date-insert-value')->count() ? $crawler->filter('.date-insert-value')->text() : 'No date found';
      $dateUpdated = $crawler->filter('.date-update-value')->count() ? $crawler->filter('.date-update-value')->text() : '';
      $author = $crawler->filter('.event-info-value.event-author')->count() ? $crawler->filter('.event-info-value.event-author')->text() : 'No author found';
      $textElements = $crawler->filter('.event-text.editor p');
      $annotationElement = $crawler->filter('#action-detail');
      $imageElements = $crawler->filter('.card-img-top');
      $topImgElements = $crawler->filter('.img-fluid');
      $attachmentElements = $crawler->filter('.link-small');

      $text = $textElements->each(function (Crawler $node) {
        return trim(preg_replace('/\s+/', ' ', $node->text()));
      });
      $text = implode("\n", $text);

      $annotation = $annotationElement->count() ? $annotationElement->text() : '';

      $images = $imageElements->each(function (Crawler $node) use ($baseUrl) {
        $relativeImageUrl = $node->attr('src');
        $absoluteImageUrl = (strpos($relativeImageUrl, 'http') === 0) ? $relativeImageUrl : $baseUrl . ltrim($relativeImageUrl, '/');
        return $absoluteImageUrl;
      });

      $topImages = $topImgElements->each(function (Crawler $node) use ($baseUrl) {
        $relativeImageUrl = $node->attr('src');
        $absoluteImageUrl = (strpos($relativeImageUrl, 'http') === 0) ? $relativeImageUrl : $baseUrl . ltrim($relativeImageUrl, '/');
        return $absoluteImageUrl;
      });

      $attachments = $attachmentElements->each(function (Crawler $node) use ($baseUrl, $client, $pdfDir, $filesystem) {
        $relativeUrl = $node->attr('href');
        $absoluteUrl = strpos($relativeUrl, 'http') === 0 ? $relativeUrl : $baseUrl . ltrim($relativeUrl, '/');
        $title = trim($node->text());
        $pdfResponse = $client->get($absoluteUrl);
        $pdfContent = $pdfResponse->getBody()->getContents();
        $pdfFileName = sanitizeFilename($title) . '.pdf';
        $pdfFilePath = $pdfDir . '/' . $pdfFileName;
        $filesystem->write($pdfFilePath, $pdfContent);
        return new FileDto(
          $absoluteUrl,
          $pdfFileName,
          $title,
          'application/pdf',
          'pdf',
          strlen($pdfContent)
        );
      });

      echo "Scraped: $title\n";

      $sanitizedTitle = sanitizeFilename($title);

      $photoDtos = [];
      foreach ($images as $index => $imageUrl) {
        $imageFileName = "{$sanitizedTitle}_" . ($index + 1) . ".jpg";
        $imageFilePath = $imagesDir . '/' . $imageFileName;

        $imageResponse = $client->get($imageUrl);
        $imageContent = $imageResponse->getBody()->getContents();

        $filesystem->write($imageFilePath, $imageContent);
        $photoDtos[] = new FileDto(
          $imageUrl,
          $imageFileName,
          '',
          'image/jpeg',
          'jpg',
          strlen($imageContent)
        );
      }

      $topPhotoDtos = [];
      foreach ($topImages as $index => $imageUrl) {
        $imageFileName = "{$sanitizedTitle}_top_" . ($index + 1) . ".jpg";
        $imageFilePath = $imagesDir . '/' . $imageFileName;

        $imageResponse = $client->get($imageUrl);
        $imageContent = $imageResponse->getBody()->getContents();

        $filesystem->write($imageFilePath, $imageContent);
        $topPhotoDtos[] = new FileDto(
          $imageUrl,
          $imageFileName,
          '',
          'image/jpeg',
          'jpg',
          strlen($imageContent)
        );
      }

      $createdAt = parseDate($dateUploaded);
      $updatedAt = $dateUpdated ? parseDate($dateUpdated) : $createdAt;

      $postDto = new PostDto(
        $post['url'],
        $title,
        $topPhotoDtos[0] ?? null,
        $annotation,
        $text,
        $createdAt,
        $updatedAt,
        array_merge($photoDtos, $topPhotoDtos),
        $attachments
      );

      $data[] = $postDto;
    } catch (Exception $e) {
      echo "Error scraping post: {$post['url']} - " . $e->getMessage() . "\n";
    }
  }
}

file_put_contents($outputDir . '/data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "Scraping completed successfully.\n";
