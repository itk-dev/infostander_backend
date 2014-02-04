<?php
namespace Infostander\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @ORM\Table(name="image_slide")
 * @Vich\Uploadable
 */
class Slide
{
  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\Column(type="datetime", name="updated_at")
   *
   * @var \DateTime $updatedAt
   */
  protected $updatedAt;

  /**
   * @ORM\Column(type="string", length=255)
   * @Assert\NotBlank()
   */
  protected $title;

  /**
   * @ORM\Column(type="text")
   */
  protected $description;

  /**
   * @Assert\File(
   *     maxSize="10M",
   *     mimeTypes={"image/png", "image/jpeg", "image/pjpeg"}
   * )
   * @Vich\UploadableField(mapping="image_slide", fileNameProperty="imageName")
   *
   * @var File $image
   */
  protected $image;

  /**
   * @ORM\Column(type="string", length=255, name="image_name")
   *
   * @var string $imageName
   */
  protected $imageName;




  public function getId() {
    return $this->id;
  }

  public function getTitle() {
    return $this->title;
  }

  public function setTitle($title) {
    $this->title = $title;
  }

  public function getDescription() {
    return $this->description;
  }

  public function setDescription($description) {
    $this->description = $description;
  }

  public function getImage() {
    return $this->image;
  }

  public function setImage($image)
  {
    $this->image = $image;

    if ($this->image) {
      $this->updatedAt = new \DateTime('now');
    }
  }

  public function getImageName() {
    return $this->imageName;
  }

  public function setImageName($imageName) {
    $this->imageName = $imageName;
  }

  public function getUpdatedAt() {
    return $this->updatedAt;
  }

  public function setUpdatedAt($updatedAt) {
    $this->updatedAt = $updatedAt;
  }
}