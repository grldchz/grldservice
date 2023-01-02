# grldservice now inserts a watermark on all uploaded images.
# This script watermarks all previously uploaded images.
# Performance is not good.  Took several minutes to watermark 337 images.
# First create a media.txt with the result of this SQL
# SELECT concat(contents.user_name,"/",contents.id,"/src/",file) FROM media
#     join contents on contents.id = content_id
#     where file not like "%.mp4";
# Run this file whereever as long as media.txt is in the same folder
# Run the resulting watermarkCmds.sh inside grldservice/media folder
path=.
src_filename=
src_filepath=
output_filepath=
current_year=$(date +%Y)
while read MEDIAFILE; do
    IFS='/' read -ra ARR <<< "$MEDIAFILE"
    #src_filename=$(echo $MEDIAFILE| cut -d'/' -f 3)
    src_filename=${ARR[3]}
    #echo "src_filename=$src_filename"
    src_filepath="$path/$MEDIAFILE"
    #echo "$src_filepath"
    output_filepath="$path/${ARR[0]}/${ARR[1]}/img_profile_$src_filename.jpeg"
    #echo "output_filepath=$output_filepath"
    echo "convert -strip -define jpeg:size=600x450 $src_filepath -auto-orient -thumbnail 200x150 -unsharp 0x.5 -font DejaVu-Sans -pointsize 4 -draw \"gravity southwest fill black text 0,2 'Copyright $current_year Grilled Cheese of the Day' fill white  text 1,1 'Copyright $current_year Grilled Cheese of the Day'\"" $output_filepath >> watermarkCmds.sh
    output_filepath="$path/${ARR[0]}/${ARR[1]}/img_slide_$src_filename.jpeg"
    #echo "output_filepath=$output_filepath"
    echo "convert -strip -define jpeg:size=1600x1200 $src_filepath -auto-orient -thumbnail 600x450 -unsharp 0x.5 -font DejaVu-Sans -pointsize 8 -draw \"gravity southwest fill black text 0,2 'Copyright $current_year Grilled Cheese of the Day' fill white  text 1,1 'Copyright $current_year Grilled Cheese of the Day'\"" $output_filepath >> watermarkCmds.sh
    output_filepath="$path/${ARR[0]}/${ARR[1]}/img_full_$src_filename.jpeg"
    #echo "output_filepath=$output_filepath"
    echo "convert -strip -define jpeg:size=1600x1200 $src_filepath -auto-orient -thumbnail 1600x1200 -unsharp 0x.5 -font DejaVu-Sans -pointsize 16 -draw \"gravity southwest fill black text 0,2 'Copyright $current_year Grilled Cheese of the Day' fill white  text 1,1 'Copyright $current_year Grilled Cheese of the Day'\"" $output_filepath >> watermarkCmds.sh
    
done <media.txt